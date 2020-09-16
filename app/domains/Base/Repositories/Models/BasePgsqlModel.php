<?php

namespace App\Domains\Base\Repositories\Models;

use App\Domains\Base\Repositories\Models\Traits\ShortUuid;

/**
 * Class BasePgsqlModel
 *
 * @package App\Models
 */
class BasePgsqlModel extends AbstractModel
{
    use ShortUuid;

    public $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $connection = 'pgsql';
}
