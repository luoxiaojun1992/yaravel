<?php

namespace App\Domains\Base\Repositories\Models;

use App\Domains\Base\Repositories\Models\Traits\ShortUuid;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class BaseMysqlModel
 *
 * @property string $jing_uuid
 * @package App\Models
 */
class BaseMysqlModel extends AbstractModel
{
    use ShortUuid;
    use SoftDeletes;

    public $primaryKey = 'jing_uuid';
    public $incrementing = false;

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'created_timestamp';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'updated_timestamp';

    protected $dates = [self::CREATED_AT, self::UPDATED_AT];
    protected $connection = 'mysql';

    public function __construct(array $attributes = [])
    {
        $this->setKeyName(config('uuid.default_uuid_column'));
        if (!in_array($this->getKeyName(), $this->fillable)) {
            array_push($this->fillable, $this->getKeyName());
        }
        parent::__construct($attributes);
    }

    /**
     * Before insert model data
     *
     * @param bool $validate
     * @throws \Exception
     * @return bool|null
     */
    protected function beforeCreate($validate = true)
    {
        $this->setUUID();

        return parent::beforeCreate($validate);
    }

    /**
     * Before save model data
     */
    protected function beforeSave()
    {
        $this->updateUUID();

        parent::beforeSave();
    }
}
