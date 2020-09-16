<?php

namespace App\Domains\User\Repositories\Customer;

use App\Domains\Base\Repositories\Models\OldBaseMysqlModel;

class Customer extends OldBaseMysqlModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer';

    const STATUS_ACTIVE = 1;
    const INVALID_CUSTOMER = 0;
    const SUCCESS = 0;
    const ERROR = 1;

    //connect_method
    const CONNECT_METHOD_ACCOUNT_DEV = 1;
    const CONNECT_METHOD_OPEN_PLATFORM = 2;
    //using_abroad_api
    const USING_ABROAD_API = 1;
    const NOT_USING_ABROAD_API = 2;
    //account_industry
    const RETAIL = 1;
    const HOSPITALITY = 2;
    const INDUSTRY_OTHER2C = 3;
    const INDUSTRY_OTHER2B = 4;
    const OTHER = 4;
    //is_account_admin
    const NOT_ACCOUNT_ADMIN = 0;
    const IS_ACCOUNT_ADMIN = 1;
    //oauth_site_type
    const MAGENTO = 1;
    //action_type
    const EDIT_USER = 1;
    const EDIT_STATUS = 2;
}
