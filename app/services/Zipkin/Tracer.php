<?php

namespace App\Services\Zipkin;

use App\Services\Http\Request;
use App\Support\Traits\PhpSapi;
use Psr\Http\Message\RequestInterface;
use Zipkin\Endpoint;
use Zipkin\Propagation\DefaultSamplingFlags;
use Zipkin\Propagation\RequestHeaders;
use Zipkin\Propagation\TraceContext;
use Zipkin\Reporters\Http;
use Zipkin\Samplers\BinarySampler;
use Zipkin\Span;
use const Zipkin\Kind\CLIENT;
use const Zipkin\Kind\SERVER;
use const Zipkin\Tags\ERROR;
use Zipkin\Tracing;
use Zipkin\TracingBuilder;

/**
 * Class Tracer
 * @package Jing\Yaf\Zipkin
 */
class Tracer
{
    use PhpSapi;

    const HTTP_REQUEST_BODY = 'http.request.body';
    const HTTP_REQUEST_BODY_SIZE = 'http.request.body.size';
    const HTTP_REQUEST_HEADERS = 'http.request.headers';
    const HTTP_REQUEST_PROTOCOL_VERSION = 'http.request.protocol.version';
    const HTTP_REQUEST_SCHEME = 'http.request.scheme';
    const HTTP_RESPONSE_BODY = 'http.response.body';
    const HTTP_RESPONSE_BODY_SIZE = 'http.response.body.size';
    const HTTP_RESPONSE_HEADERS = 'http.response.headers';
    const HTTP_RESPONSE_PROTOCOL_VERSION = 'http.response.protocol.version';
    const RUNTIME_START_SYSTEM_LOAD = 'runtime.start_system_load';
    const RUNTIME_FINISH_SYSTEM_LOAD = 'runtime.finish_system_load';
    const RUNTIME_MEMORY = 'runtime.memory';
    const RUNTIME_PHP_VERSION = 'runtime.php.version';
    const DB_QUERY_TIMES = 'db.query.times';
    const DB_QUERY_TOTAL_DURATION = 'db.query.total.duration';
    const FRAMEWORK_VERSION = 'framework.version';
    const HTTP_QUERY_STRING = 'http.query_string';

    public $serviceName = 'Yaf';
    public $endpointUrl = 'http://localhost:9411/api/v2/spans';
    public $sampleRate = 0;
    public $bodySize = 500;
    public $curlTimeout = 1;
    public $redisOptions = [
        'queue_name' => 'queue:zipkin:span',
        'connection' => 'zipkin',
    ];
    public $reportType = 'http';

    /** @var array TraceContext[] */
    public $contextStack = [];

    /** @var \Zipkin\Tracer */
    private $tracer;

    /** @var Tracing */
    private $tracing;

    /** @var Request */
    protected $request;

    /**
     * Tracer constructor.
     * @param $config
     * @param null|Request $request
     */
    public function __construct($config, $request = null)
    {
        if (isset($config['service_name'])) {
            $this->serviceName = $config['service_name'];
        }
        if (isset($config['endpoint_url'])) {
            $this->endpointUrl = $config['endpoint_url'];
        }
        if (isset($config['sample_rate'])) {
            $this->sampleRate = doubleval($config['sample_rate']);
        }
        if (isset($config['body_size'])) {
            $this->bodySize = intval($config['body_size']);
        }
        if (isset($config['curl_timeout'])) {
            $this->curlTimeout = intval($config['curl_timeout']);
        }
        if (isset($config['redis_options'])) {
            $this->redisOptions = array_merge($this->redisOptions, $config['redis_options']);
        }
        if (isset($config['report_type'])) {
            $this->reportType = $config['report_type'];
        }

        $this->request = $request;

        $this->createTracer();
    }

    /**
     * Create zipkin tracer
     */
    private function createTracer()
    {
        if (!$this->isCli()) {
            $ip = $this->request->ip();
            $isIpV6 = $this->request->isIpV6();
            $remotePort = $this->request->clientPort();
            $endpoint = Endpoint::create(
                $this->serviceName,
                (!$isIpV6) ? $ip : null,
                $isIpV6 ? $ip : null,
                (!is_null($remotePort)) ? ((int)$remotePort) : $remotePort
            );
        } else {
            $endpoint = Endpoint::create($this->serviceName);
        }
        $sampler = BinarySampler::createAsAlwaysSample();

        $this->tracing = TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
            ->havingSampler($sampler)
            ->havingReporter($this->getReporter())
            ->build();
        $this->tracer = $this->getTracing()->getTracer();
    }

    private function getReporter()
    {
        if ($this->reportType === 'redis') {
            return new RedisReporter($this->redisOptions);
        } elseif ($this->reportType === 'http') {
            return new Http(null, ['endpoint_url' => $this->endpointUrl, 'timeout' => $this->curlTimeout]);
        }

        return new Http(null, ['endpoint_url' => $this->endpointUrl, 'timeout' => $this->curlTimeout]);
    }

    /**
     * @return Tracing
     */
    public function getTracing()
    {
        return $this->tracing;
    }

    /**
     * @return \Zipkin\Tracer
     */
    public function getTracer()
    {
        return $this->tracer;
    }

    /**
     * Create a server trace
     *
     * @param string $name
     * @param callable $callback
     * @param bool $flush
     * @return mixed
     * @throws \Exception
     */
    public function serverSpan($name, $callback, $flush = false)
    {
        return $this->span($name, $callback, SERVER, $flush);
    }

    /**
     * Create a client trace
     *
     * @param string $name
     * @param callable $callback
     * @param bool $flush
     * @return mixed
     * @throws \Exception
     */
    public function clientSpan($name, $callback, $flush = false)
    {
        return $this->span($name, $callback, CLIENT, $flush);
    }

    /**
     * Create a trace
     *
     * @param string $name
     * @param callable $callback
     * @param null|string $kind
     * @param bool $flush
     * @return mixed
     * @throws \Exception
     */
    public function span($name, $callback, $kind = null, $flush = false)
    {
        $parentContext = $this->getParentContext();
        $span = $this->getSpan($parentContext);
        $span->setName($name);
        if ($kind) {
            $span->setKind($kind);
        }

        $span->start();

        $spanContext = $span->getContext();
        array_push($this->contextStack, $spanContext);

        $startMemory = 0;
        if ($span->getContext()->isSampled()) {
            $startMemory = memory_get_usage();
            $this->beforeSpanTags($span);
        }

        try {
            return call_user_func_array($callback, ['span' => $span]);
        } catch (\Exception $e) {
            if ($span->getContext()->isSampled()) {
                $this->addTag($span, ERROR, $e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
            throw $e;
        } finally {
            if ($span->getContext()->isSampled()) {
                $this->addTag($span, static::RUNTIME_MEMORY, round((memory_get_usage() - $startMemory) / 1000000, 2) . 'MB');
                $this->afterSpanTags($span);
            }

            $span->finish();
            array_pop($this->contextStack);

            if ($flush) {
                $this->flushTracer();
            }
        }
    }

    /**
     * Formatting http protocol version
     *
     * @param $protocolVersion
     * @return string
     */
    public function formatHttpProtocolVersion($protocolVersion)
    {
        if (stripos($protocolVersion, 'HTTP/') !== 0) {
            return 'HTTP/' . $protocolVersion;
        }

        return strtoupper($protocolVersion);
    }

    /**
     * Formatting http path
     *
     * @param $httpPath
     * @return string
     */
    public function formatHttpPath($httpPath)
    {
        if (strpos($httpPath, '/') !== 0) {
            $httpPath = '/' . $httpPath;
        }

        return $httpPath;
    }

    /**
     * Formatting http body
     *
     * @param $httpBody
     * @param null $bodySize
     * @return string
     */
    public function formatHttpBody($httpBody, $bodySize = null)
    {
        $httpBody = $this->convertToStr($httpBody);

        if (is_null($bodySize)) {
            $bodySize = strlen($httpBody);
        }

        if ($bodySize > $this->bodySize) {
            $httpBody = mb_substr($httpBody, 0, $this->bodySize, 'utf8') . ' ...';
        }

        return $httpBody;
    }

    /**
     * Formatting http path
     *
     * @param $httpPath
     * @return string|string[]|null
     */
    public function formatRoutePath($httpPath)
    {
        $httpPath = preg_replace('/\/\d+$/', '/{id}', $httpPath);
        $httpPath = preg_replace('/\/\d+\//', '/{id}/', $httpPath);

        return $httpPath;
    }

    /**
     * Add span tag
     *
     * @param Span $span
     * @param $key
     * @param $value
     */
    public function addTag($span, $key, $value)
    {
        $span->tag($key, $this->convertToStr($value));
    }

    /**
     * Convert variable to string
     *
     * @param $value
     * @return string
     */
    public function convertToStr($value)
    {
        if (!is_scalar($value)) {
            $value = '';
        } else {
            $value = (string)$value;
        }

        return $value;
    }

    /**
     * Inject trace context to psr request
     *
     * @param TraceContext $context
     * @param RequestInterface $request
     */
    public function injectContextToRequest($context, &$request)
    {
        $injector = $this->getTracing()->getPropagation()->getInjector(new RequestHeaders());
        $injector($context, $request);
    }

    /**
     * Extract trace context from headers
     *
     * @param Request $request
     * @return TraceContext|DefaultSamplingFlags
     */
    public function extractRequestToContext($request)
    {
        $extractor = $this->getTracing()->getPropagation()->getExtractor(new YafRequestHeaders());
        return $extractor($request);
    }

    /**
     * @return DefaultSamplingFlags|TraceContext|null
     */
    public function getParentContext()
    {
        $parentContext = null;
        $contextStackLen = count($this->contextStack);
        if ($contextStackLen > 0) {
            $parentContext = $this->contextStack[$contextStackLen - 1];
        } else {
            if (!$this->isCli()) {
                //Extract trace context from request
                $parentContext = $this->extractRequestToContext($this->request);
            }
        }

        return $parentContext;
    }

    /**
     * @param TraceContext|DefaultSamplingFlags $parentContext
     * @return \Zipkin\Span
     */
    public function getSpan($parentContext)
    {
        $tracer = $this->getTracer();

        if (!$parentContext) {
            $span = $tracer->newTrace($this->getDefaultSamplingFlags());
        } else {
            if ($parentContext instanceof TraceContext) {
                $span = $tracer->newChild($parentContext);
            } else {
                if (is_null($parentContext->isSampled())) {
                    $samplingFlags = $this->getDefaultSamplingFlags();
                } else {
                    $samplingFlags = $parentContext;
                }

                $span = $tracer->newTrace($samplingFlags);
            }
        }

        return $span;
    }

    /**
     * @return DefaultSamplingFlags
     */
    private function getDefaultSamplingFlags()
    {
        $sampleRate = $this->sampleRate;
        if ($sampleRate >= 1) {
            $samplingFlags = DefaultSamplingFlags::createAsEmpty(); //Sample config determined by sampler
        } elseif ($sampleRate <= 0) {
            $samplingFlags = DefaultSamplingFlags::createAsNotSampled();
        } else {
            mt_srand(time());
            if (mt_rand() / mt_getrandmax() <= $sampleRate) {
                $samplingFlags = DefaultSamplingFlags::createAsEmpty(); //Sample config determined by sampler
            } else {
                $samplingFlags = DefaultSamplingFlags::createAsNotSampled();
            }
        }

        return $samplingFlags;
    }

    /**
     * @param Span $span
     */
    private function startSysLoadTag($span)
    {
        $startSystemLoad = sys_getloadavg();
        foreach ($startSystemLoad as $k => $v) {
            $startSystemLoad[$k] = round($v, 2);
        }
        $this->addTag($span, static::RUNTIME_START_SYSTEM_LOAD, implode(',', $startSystemLoad));
    }

    /**
     * @param Span $span
     */
    private function finishSysLoadTag($span)
    {
        $finishSystemLoad = sys_getloadavg();
        foreach ($finishSystemLoad as $k => $v) {
            $finishSystemLoad[$k] = round($v, 2);
        }
        $this->addTag($span, static::RUNTIME_FINISH_SYSTEM_LOAD, implode(',', $finishSystemLoad));
    }

    /**
     * @param Span $span
     */
    public function beforeSpanTags($span)
    {
        $this->addTag($span, self::FRAMEWORK_VERSION, 'Yaf-' . \Yaf\VERSION);
        $this->addTag($span, self::RUNTIME_PHP_VERSION, PHP_VERSION);
    }

    /**
     * @param Span $span
     */
    public function afterSpanTags($span)
    {
        //
    }

    public function flushTracer()
    {
        try {
            $this->getTracer()->flush();
        } catch (\Exception $e) {
            \Log::error('Zipkin report error ' . $e->getMessage());
        }
    }

    public function __destruct()
    {
        $this->flushTracer();
    }
}
