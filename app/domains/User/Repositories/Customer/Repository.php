<?php

namespace App\Domains\User\Repositories\Customer;

use App\Domains\Base\Repositories\BaseRepository;

/**
 * Class Repository
 *
 * {@inheritdoc}
 *
 * Database operations
 *
 * @package App\Domains\User\Repositories\Customer
 */
class Repository extends BaseRepository
{
    /** @var string $model */
    protected $model = Customer::class; //model class
    protected $filterable = ['*'];
}
