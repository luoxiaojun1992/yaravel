<?php

use App\Services\Zipkin\Tracer;
use Yaf\Plugin_Abstract as YafPlugin;
use Yaf\Request_Abstract as YafRequest;
use Yaf\Response_Abstract as YafResponse;
use const Zipkin\Kind\SERVER;
use Zipkin\Span;
use const Zipkin\Tags\ERROR;
use const Zipkin\Tags\HTTP_HOST;
use const Zipkin\Tags\HTTP_METHOD;
use const Zipkin\Tags\HTTP_PATH;
use const Zipkin\Tags\HTTP_STATUS_CODE;

class ZipkinPlugin extends YafPlugin
{
    /** @var Tracer */
    private $tracer;

    private $startMemory = 0;

    /** @var Span */
    private $span;

    private $config;

    //cache
    private $oldApiPrefix;
    private $oldUri;
    private $needSample;

    public function __construct()
    {
        $this->config = Config::get('zipkin');
    }

    private function needSample(YafRequest $yafRequest)
    {
        $apiPrefix = !empty($this->config['api_prefix']) ? explode(',', $this->config['api_prefix']) : ['/api', '/internal/'];

        $uri = $yafRequest->getRequestUri();
        if (stripos($uri, '/') !== 0) {
            $uri = '/' . $uri;
        }

        //Cache
        if ($apiPrefix === $this->oldApiPrefix) {
            if ($uri === $this->oldUri) {
                if (!is_null($this->needSample)) {
                    return $this->needSample;
                }
            }
        }

        //Real
        $this->oldApiPrefix = $apiPrefix;
        $this->oldUri = $uri;

        foreach ($apiPrefix as $prefix) {
            if (stripos($uri, $prefix) === 0) {
                return ($this->needSample = true);
            }
        }

        return ($this->needSample = false);
    }

    public function routerStartup(YafRequest $yafRequest, YafResponse $response)
    {
        if (!$this->needSample($yafRequest)) {
            return;
        }

        /** @var Tracer $yafTracer */
        $this->tracer = \Registry::get('services.zipkin');

        $this->startSpan($yafRequest);

        if ($this->span->getContext()->isSampled()) {
            //Maybe not useful for swoole
            $previousHandler = set_exception_handler(function () {});
            set_exception_handler(function (Throwable $e) use ($previousHandler, $yafRequest, $response) {
                $this->exceptionHandler($e, $yafRequest, $response, $previousHandler);
            });

            $this->tracer->addTag($this->span, HTTP_HOST, $this->getHttpHost($yafRequest));
            $this->tracer->addTag($this->span, HTTP_PATH, $this->getRequestUri($yafRequest));
            $this->tracer->addTag($this->span, Tracer::HTTP_QUERY_STRING, (string)$yafRequest->getServer('QUERY_STRING'));
            $this->tracer->addTag($this->span, HTTP_METHOD, $yafRequest->getMethod());
            $httpRequestBody = $this->tracer->convertToStr(file_get_contents('php://input'));
            $httpRequestBodyLen = strlen($httpRequestBody);
            $this->tracer->addTag($this->span, Tracer::HTTP_REQUEST_BODY_SIZE, $httpRequestBodyLen);
            $this->tracer->addTag($this->span, Tracer::HTTP_REQUEST_BODY, $this->tracer->formatHttpBody(
                $httpRequestBody,
                $httpRequestBodyLen
            ));
            $headers = [];
            foreach ($_SERVER as $key => $value) {
                if (stripos($key, 'HTTP_') === 0) {
                    $headers[strtolower(str_replace('_', '-', substr($key, strlen('HTTP_'))))] = [$value];
                } elseif (in_array(strtoupper($key), ['CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE', 'REMOTE_ADDR', 'SERVER_PORT', 'HTTPS'])) {
                    $headers[strtolower(str_replace('_', '-', $key))] = [$value];
                }
            }
            $this->tracer->addTag($this->span, Tracer::HTTP_REQUEST_HEADERS, json_encode($headers, JSON_UNESCAPED_UNICODE));
            $this->tracer->addTag(
                $this->span,
                Tracer::HTTP_REQUEST_PROTOCOL_VERSION,
                $this->tracer->formatHttpProtocolVersion($yafRequest->getServer('SERVER_PROTOCOL'))
            );
            $this->tracer->addTag($this->span, Tracer::HTTP_REQUEST_SCHEME, $this->getIsSecureConnection($yafRequest) ? 'https' : 'http');
        }
    }

    /**
     * @param \Yaf\Request_Abstract  $request
     * @param \Yaf\Response_Abstract $response
     */
    public function routerShutdown(YafRequest $request, YafResponse $response)
    {
        /* 路由完成后，在这个钩子里，你可以做登陆检测等功能*/
        if (!$this->needSample($request)) {
            return;
        }

        if ($this->span && $this->tracer) {
            $this->span->setName($this->tracer->formatHttpPath(Request::route()));
        }
    }

    /**
     * Start a trace
     *
     * @param YafRequest $yafRequest
     */
    private function startSpan(YafRequest $yafRequest)
    {
        $parentContext = $this->tracer->getParentContext();

        $this->span = $this->tracer->getSpan($parentContext);
        $this->span->setName($this->tracer->formatHttpPath(Request::route()));
        $this->span->setKind(SERVER);
        $this->span->start();
        array_push($this->tracer->contextStack, $this->span->getContext());

        if ($this->span->getContext()->isSampled()) {
            $this->tracer->beforeSpanTags($this->span);
        }
    }

    /**
     * Add tags before finishing trace
     *
     * @param YafRequest $yafRequest
     * @param YafResponse $yafResponse
     */
    private function finishSpanTag(YafRequest $yafRequest, YafResponse $yafResponse)
    {
        if ($yafResponse) {
            $responseResId = 'context.http.response';
            if (Registry::has($responseResId)) {
                /** @var \App\Services\Http\Response $response */
                $response = Registry::get($responseResId);
            } else {
                $response = null;
            }
            if (!is_null($response)) {
                $httpStatusCode = $response->getStatusCode();
            } else {
                $httpStatusCode = 200;
            }
            if ($httpStatusCode >= 500 && $httpStatusCode < 600) {
                $this->tracer->addTag($this->span, ERROR, 'server error');
            } elseif ($httpStatusCode >= 400 && $httpStatusCode < 500) {
                $this->tracer->addTag($this->span, ERROR, 'client error');
            }
            $this->tracer->addTag($this->span, HTTP_STATUS_CODE, $httpStatusCode);
            $httpResponseBody = $this->tracer->convertToStr($yafResponse->getBody());
            $httpResponseBodyLen = strlen($httpResponseBody);
            $this->tracer->addTag($this->span, Tracer::HTTP_RESPONSE_BODY_SIZE, $httpResponseBodyLen);
            $this->tracer->addTag($this->span, Tracer::HTTP_RESPONSE_BODY, $this->tracer->formatHttpBody(
                $httpResponseBody,
                $httpResponseBodyLen
            ));
            if (!is_null($response)) {
                $headers = $response->getHeaders();
                $transformedHeaders = [];
                foreach ($headers as $key => $value) {
                    $transformedHeaders[strtolower($key)] = $value;
                }
                $this->tracer->addTag(
                    $this->span,
                    Tracer::HTTP_RESPONSE_HEADERS,
                    json_encode($transformedHeaders, JSON_UNESCAPED_UNICODE)
                );
            }
            $this->tracer->addTag(
                $this->span,
                Tracer::HTTP_RESPONSE_PROTOCOL_VERSION,
                $this->tracer->formatHttpProtocolVersion($yafRequest->getServer('SERVER_PROTOCOL'))
            );
        }
        $this->tracer->afterSpanTags($this->span);
    }

    /**
     * Finish a trace
     */
    private function finishSpan()
    {
        $this->span->finish();
        array_pop($this->tracer->contextStack);
        $this->tracer->flushTracer();

    }

    public function dispatchLoopShutdown(YafRequest $request, YafResponse $response)
    {
        if (!$this->needSample($request)) {
            return;
        }

        if ($this->span && $this->tracer) {
            if ($this->span->getContext()->isSampled()) {
                $this->finishSpanTag($request, $response);
            }
            $this->finishSpan();
        }
    }

    /**
     * @param YafRequest $yafRequest
     * @return string
     */
    private function getHttpHost(YafRequest $yafRequest)
    {
        if (!$httpHost = $yafRequest->getServer('HTTP_HOST')) {
            if (!$httpHost = $yafRequest->getServer('SERVER_NAME')) {
                $httpHost = $yafRequest->getServer('SERVER_ADDR');
            }
        }

        return $httpHost ?: '';
    }

    /**
     * Return if the request is sent via secure channel (https).
     * @param YafRequest $yafRequest
     * @return bool if the request is sent via secure channel (https)
     */
    private function getIsSecureConnection(YafRequest $yafRequest)
    {
        $https = $yafRequest->getServer('HTTPS');
        if (isset($https) && (strcasecmp($https, 'on') === 0 || $https == 1)) {
            return true;
        }

        $secureProtocolHeaders = [
            'X-Forwarded-Proto' => ['https'], // Common
            'Front-End-Https' => ['on'], // Microsoft
        ];
        foreach ($secureProtocolHeaders as $header => $values) {
            $header = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
            if (($headerValue = isset($_SERVER[$header]) ? $_SERVER[$header] : null) !== null) {
                foreach ($values as $value) {
                    if (strcasecmp($headerValue, $value) === 0) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function getRequestUri(YafRequest $yafRequest)
    {
        $uri = $this->tracer->formatHttpPath($yafRequest->getRequestUri());
        if (strpos($uri, '?')) {
            $pathInfo = parse_url($uri);
            if (isset($pathInfo['path'])) {
                return $pathInfo['path'];
            }
        }

        return $uri;
    }

    private function exceptionHandler(Throwable $e, YafRequest $yafRequest, YafResponse $yafResponse, $previousHandler)
    {
        if ($previousHandler) {
            call_user_func($previousHandler, $e);
        }

        if (!$this->needSample($yafRequest)) {
            return;
        }

        if ($this->span && $this->tracer) {
            if (Request::hasRoute()) {
                $this->span->setName($this->tracer->formatHttpPath(Request::route()));
            }

            if ($this->span->getContext()->isSampled()) {
                $this->finishSpanTag($yafRequest, $yafResponse);
            }
            $this->finishSpan();
        }
    }
}
