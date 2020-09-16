<?php

namespace App\Domains\User\Services;

use App\Domains\Base\Services\BaseService;

/**
 * Class UserService
 *
 * {@inheritdoc}
 *
 * User api service
 *
 * @package App\Domains\User\Services
 */
class UserService extends BaseService
{
    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function getUserIdAndMid()
    {
        return $this->callV2Api('/api/user/users/getuseridandmid');
    }

    /**
     * @param $customerId
     * @return array|mixed
     * @throws \Exception
     */
    public function getCustomerDetail($customerId)
    {
        return $this->callV2Api('/api/user/customers/getdetail/' . $customerId);
    }

    /**
     * @param $customerId
     * @return array|mixed
     * @throws \Exception
     */
    public function getAppId($customerId)
    {
        return $this->callWithStaticToken(
            $customerId,
            '/api/user/customers/getappid/' . $customerId
        );
    }

    /**
     * 获取脚本中 api 调用鉴权需要的 jwt token
     *
     * @param $username
     * @param $password
     * @return mixed|string
     * @throws \Exception
     */
    public function getCommandToken($username, $password)
    {
        if (!$username || !$password) {
            throw new \Exception('invalid V2 API user config');
        }

        $response = $this->callV2ApiWithoutAuth(
            '/api/user/users/thirdpartyuserlogin',
            'POST',
            ['username' => $username, 'password' => $password]
        );

        if (isset($response['authorization']) && is_string($response['authorization'])) {
            $authorization = $response['authorization'];
            $expiresIn = $response['expires_in'];

            return [$authorization, $expiresIn];
        } else {
            throw new \Exception('invalid authorization return', 401);
        }
    }
}
