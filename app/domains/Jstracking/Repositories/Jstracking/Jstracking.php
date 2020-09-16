<?php

namespace App\Domains\Jstracking\Repositories\Jstracking;

use App\Domains\Base\Repositories\Models\BaseMysqlModel;

class Jstracking extends BaseMysqlModel
{
    protected $table = 'jstracking';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mid', 'type', 'name', 'code', 'tracking', 'status', 'owner_id', 'mini_version', 'appid','old_version',
        self::CREATED_AT, self::UPDATED_AT,
    ];
}
