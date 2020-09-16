<?php

namespace App\Domains\Base\Repositories\Models;

/**
 * Class OldBaseMysqlModel
 *
 * {@inheritdoc}
 * ！！！注意
 * 兼容老的数据库表，新建的数据库表Model继承BaseMysqlModel类
 * 继承时请确认时间字段不冲突
 *
 * @package App\Models
 */
class OldBaseMysqlModel extends AbstractModel
{
    protected $connection = 'yii2mysql';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'create_time';

    const UPDATED_AT =  null;

    protected $dates = [self::CREATED_AT];
}
