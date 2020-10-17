<?php

namespace App\Domains\Base\Services;

use App\Consts\Errors;
use App\Domains\Common\Support\Auth;
use App\Domains\User\Support\Customer;
use App\Support\Traits\PhpSapi;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use App\Services\Zipkin\HttpClient;

/**
 * Class BaseService
 *
 * {@inheritdoc}
 *
 * Base api service
 *
 * @package App\Domains\Base
 */
class BaseService
{
    use PhpSapi;

    protected $baseUrl = '';

    protected $internalBaseUrl = '';

    protected $withOperationName = true;

    public function __construct()
    {
        $this->baseUrl = config('api.v2_domain');
        $this->internalBaseUrl = config('api.v2_internal_domain');

        \Registry::set(static::class, $this);
    }

    /**
     * Call V2 API
     *
     * @param string $uri
     * @param string $method
     * @param array $params
     * @param array $headers
     * @param string|null $jCustomerUUID
     * @param string|null $authorization
     * @param string|null $operationName
     * @param int $timeout
     * @param bool $withAuth
     * @param bool $internal
     * @return array|mixed
     * @throws \Exception
     */
    public function callV2Api(
        $uri,
        $method = 'GET',
        $params = [],
        $headers = [],
        $jCustomerUUID = null,
        $authorization = null,
        $operationName = null,
        $timeout = 30,
        $withAuth = true,
        $internal = false
    )
    {
        if ($internal) {
            if (empty($this->internalBaseUrl)) {
                \Log::error('EMPTY BASE URL');
                throw new \Exception('EMPTY BASE URL');
            }
        } else {
            if (empty($this->baseUrl)) {
                \Log::error('EMPTY BASE URL');
                throw new \Exception('EMPTY BASE URL');
            }
        }

        return $this->callApi(
            $this->baseUrl . $uri,
            $method,
            $params,
            $headers,
            $jCustomerUUID,
            $authorization,
            $operationName,
            $timeout,
            $withAuth
        );
    }

    /**
     * Call V2 API without Auth
     *
     * @param string $uri
     * @param string $method
     * @param array $params
     * @param array $headers
     * @param string|null $jCustomerUUID
     * @param string|null $authorization
     * @param string|null $operationName
     * @param int $timeout
     * @param bool $internal
     * @return array|mixed
     * @throws \Exception
     */
    public function callV2ApiWithoutAuth(
        $uri,
        $method = 'GET',
        $params = [],
        $headers = [],
        $jCustomerUUID = null,
        $authorization = null,
        $operationName = null,
        $timeout = 30,
        $internal = false
    )
    {
        return $this->callV2Api(
            $uri,
            $method,
            $params,
            $headers,
            $jCustomerUUID,
            $authorization,
            $operationName,
            $timeout,
            false,
            $internal
        );
    }

    /**
     * Call Internal API
     *
     * @param string $uri
     * @param string $method
     * @param array $params
     * @param array $headers
     * @param string|null $jCustomerUUID
     * @param string|null $authorization
     * @param string|null $operationName
     * @param int $timeout
     * @param bool $withAuth
     * @return array|mixed
     * @throws \Exception
     */
    public function callInternalApi(
        $uri,
        $method = 'GET',
        $params = [],
        $headers = [],
        $jCustomerUUID = null,
        $authorization = null,
        $operationName = null,
        $timeout = 30,
        $withAuth = false
    )
    {
        return $this->callV2Api(
            $uri,
            $method,
            $params,
            $headers,
            $jCustomerUUID,
            $authorization,
            $operationName,
            $timeout,
            $withAuth,
            true
        );
    }

    /**
     * Call Internal API
     *
     * @param string $uri
     * @param string $method
     * @param array $params
     * @param array $headers
     * @param string|null $jCustomerUUID
     * @param string|null $authorization
     * @param string|null $operationName
     * @param int $timeout
     * @return array|mixed
     * @throws \Exception
     */
    public function callInternalApiWithoutAuth(
        $uri,
        $method = 'GET',
        $params = [],
        $headers = [],
        $jCustomerUUID = null,
        $authorization = null,
        $operationName = null,
        $timeout = 30
    )
    {
        return $this->callV2ApiWithoutAuth(
            $uri,
            $method,
            $params,
            $headers,
            $jCustomerUUID,
            $authorization,
            $operationName,
            $timeout,
            true
        );
    }

    /**
     * Call Any API
     *
     * @param $url
     * @param string $method
     * @param array $params
     * @param array $headers
     * @param null $jCustomerUUID
     * @param null $authorization
     * @param null $operationName
     * @param int $timeout
     * @param bool $withAuth
     * @return array|mixed|string
     * @throws \Exception
     */
    public function callApi(
        $url,
        $method = 'GET',
        $params = [],
        $headers = [],
        $jCustomerUUID = null,
        $authorization = null,
        $operationName = null,
        $timeout = 30,
        $withAuth = false
    )
    {
        if (empty($url)) {
            \Log::error('EMPTY API URL');
            throw new \Exception('EMPTY API URL');
        }

        if (empty($jCustomerUUID)) {
            $jCustomerUUID = Customer::getCurrentUUID();
        }

        if (empty($authorization)) {
            $authorization = Auth::getCurrentAuthorization();
        }

        if ($withAuth) {
            if (empty($jCustomerUUID)) {
                \Log::error('EMPTY jCustomerUUID');
                throw new \Exception('EMPTY jCustomerUUID');
            }

            if (empty($authorization)) {
                $authorization = Auth::authorization();
            }

            if (empty($authorization)) {
                \Log::error('EMPTY Authorization');
                throw new \Exception('EMPTY Authorization');
            }
        }

        if ($jCustomerUUID) {
            $headers['J-CustomerUUID'] = $jCustomerUUID;
        }
        if ($authorization) {
            $headers['Authorization'] = $authorization;
        }

        if ($this->withOperationName || (!empty($operationName))) {
            $operationName = Auth::operationName($operationName);
        }

        if ($operationName) {
            if (strpos($url, '?') === false) {
                $url .= '?operation=' . urlencode($operationName);
            } else {
                $url .= '&operation=' . urlencode($operationName);
            }
        }

        return $this->doRequest(
            $url,
            $method,
            $headers,
            $params,
            $timeout
        );
    }

    /**
     * Call HTTP API With Static Token
     *
     * @param mixed $mid
     * @param string $uri
     * @param string $method
     * @param array $params
     * @param array $headers
     * @param int $timeout
     * @param bool $withAuth
     * @param bool $internal
     * @return array|mixed
     * @throws \Exception
     */
    public function callWithStaticToken(
        $mid,
        $uri,
        $method = 'GET',
        $params = [],
        $headers = [],
        $timeout = 30,
        $withAuth = false,
        $internal = false
    )
    {
        $jCustomerUUID = Auth::jCustomerUUID($mid);
        $authorization = Auth::getCommandToken();
        $operationName = Auth::commandOperationName();

        if ($internal) {
            return $this->callInternalApi(
                $uri,
                $method,
                $params,
                $headers,
                $jCustomerUUID,
                $authorization,
                $operationName,
                $timeout,
                $withAuth
            );
        } else {
            return $this->callV2Api(
                $uri,
                $method,
                $params,
                $headers,
                $jCustomerUUID,
                $authorization,
                $operationName,
                $timeout,
                $withAuth
            );
        }
    }

    /**
     * Call Internal HTTP API With Static Token
     *
     * @param $mid
     * @param $uri
     * @param $method
     * @param array $params
     * @param array $headers
     * @param bool $withAuth
     * @param $timeout
     * @return array|mixed
     * @throws \Exception
     */
    public function callInternalWithStaticToken(
        $mid,
        $uri,
        $method = 'GET',
        $params = [],
        $headers = [],
        $withAuth = false,
        $timeout = 30
    )
    {
        return $this->callWithStaticToken(
            $mid,
            $uri,
            $method,
            $params,
            $headers,
            $timeout,
            $withAuth,
            true
        );
    }

    /**
     * Send http request
     *
     * @param $url
     * @param string $method
     * @param array $headers
     * @param null $body
     * @param $timeout
     * @return array|mixed|string
     * @throws \Exception
     */
    protected function doRequest(
        $url,
        $method = 'GET',
        $headers = [],
        $body = null,
        $timeout = 30
    )
    {
        if (is_array($body)) {
            if (count($body) > 0) {
                $body = json_encode($body);
            } else {
                $body = null;
            }
        }

        $headers = array_merge($headers, [
            'Content-Type' => 'application/json',
            'Accept-Encoding' => 'identity, deflate, gzip',
        ]);

        $request = new GuzzleRequest($method, $url, $headers, $body, '1.1');
        $client = new HttpClient();

        $response = $client->sendWithTrace($request, ['timeout' => $timeout, 'allow_redirects' => ['max' => 10], 'decode_content' => true]);

        $result = (array)($response ? $response->getBody()->getContents() : '');
        $result = json_decode(array_pop($result), true);
        if (isset($result['code']) && ((0 == $result['code']) || ($result['code'] == 1 && isset($result['data'])))) {
            $result = isset($result['data']) ? $result['data'] : '';
        } else {
            $message = json_encode(isset($result['msg']) ? $result['msg'] : (isset($result['message']) ? $result['message'] : 'No Message Info'));
            \Log::error($message);
            throw new \Exception($message, isset($result['code']) ? $result['code'] : Errors::ERROR);
        }

        return $result;
    }
}
