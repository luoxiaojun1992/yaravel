<?php

namespace App\Domains\Base\Repositories\Models;

/**
 * Class BasePgsqlModel
 *
 * @package App\Models
 */
class BasePgsqlModel extends AbstractModel
{
    protected $connection = 'pgsql';
}
