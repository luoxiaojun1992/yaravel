<?php

namespace App\Domains\Common\Support;

use App\Consts\Cache;
use App\Consts\Errors;
use App\Domains\User\Services\UserService;
use App\Exceptions\HttpException;
use App\Support\Arr;
use App\Support\PhpSapi;

/**
 * Class Auth
 *
 * not useful for swoole
 *
 * @package App\Domains\Common\Support
 */
class Auth
{
    protected static $user;

    protected static $currentAuthorization;

    /**
     * @return mixed
     */
    public static function getCurrentAuthorization()
    {
        return self::$currentAuthorization;
    }

    /**
     * @param mixed $currentAuthorization
     */
    public static function setCurrentAuthorization($currentAuthorization)
    {
        self::$currentAuthorization = $currentAuthorization;
    }

    /**
     * Generate jCustomerUUID
     *
     * @param $mid
     * @return string
     */
    public static function jCustomerUUID($mid)
    {
        return (string)substr(md5(config('auth.user.mid_uuid_salt') . $mid), 8, 16);
    }

    public static function commandOperationName()
    {
        return 'commands';
    }

    public static function operationName($operationName = null)
    {
        if (empty($operationName)) {
            if (PhpSapi::isCli()) {
                $operationName = static::commandOperationName();
            } else {
                $operationName = \Request::controllerAction();
            }
        }

        return config('api.service_name') . '/' . $operationName;
    }

    /**
     * @return mixed|string|string[]
     * @throws \Exception
     */
    public static function authorization()
    {
        if ($authorization = static::getCurrentAuthorization()) {
            return $authorization;
        }

        if (PhpSapi::isCli()) {
            $authorization = static::getCommandToken();
        } else {
            $authorization = \Request::bearerToken() ?? \Request::authToken();
        }

        return $authorization;
    }

    /**
     * @return mixed|string
     * @throws \Exception
     */
    public static function getCommandToken()
    {
        $cacheKey = Cache::COMMAND_TOKEN;
        $redis = \RedisFacade::connection();

        $authorization = $redis->get($cacheKey);

        if (!$authorization) {
            $username = trim(config('auth.user.v2_username'));
            $password = trim(config('auth.user.v2_password'));
            if (!$username || !$password) {
                throw new \Exception('invalid V2 API user config');
            }

            /** @var UserService $userService */
            $userService = di(UserService::class);
            list($authorization, $expiresIn) = $userService->getCommandToken($username, $password);

            $redis->set($cacheKey,  $authorization, 'EX', $expiresIn - 200);
        }

        return $authorization;
    }

    public static function userInfo($field)
    {
        $user = static::user();
        return Arr::get($user, $field);
    }

    public static function mid()
    {
        return static::userInfo('mid');
    }

    public static function id()
    {
        return static::userInfo('id');
    }

    public static function appId()
    {
        return static::userInfo('app_id');
    }

    public static function componentId()
    {
        return static::userInfo('component_id');
    }

    public static function user()
    {
        if (!is_null(static::$user)) {
            return static::$user;
        }

        $authorization = static::getCurrentAuthorization();
        if (empty($authorization)) {
            if (!PhpSapi::isCli()) {
                $authorization = \Request::bearerToken() ?? \Request::authToken();
            }
        }
        if (empty($authorization)) {
            return null;
        }

        try {
            /** @var UserService $userService */
            $userService = di(UserService::class);
            $response = $userService->getUserIdAndMid();

            if (
                isset($response['userid'])
                && isset($response['currentMid'])
                && isset($response['currentAppId'])
                && isset($response['componentId'])
            ) {
                $user = [
                    'mid' => $response['currentMid'],
                    'id' => $response['userid'],
                    'app_id' => $response['currentAppId'],
                    'component_id' => $response['componentId'],
                ];
                return static::$user = $user;
            } else {
                throw new HttpException(
                    401,
                    "Invalid Token",
                    null,
                    [],
                    Errors::ERROR
                );
            }
        } catch (\Throwable $e) {
            throw new HttpException(
                401,
                "CURL ERROR: " .  $e->getMessage(),
                null,
                [],
                Errors::ERROR
            );
        }
    }
}
