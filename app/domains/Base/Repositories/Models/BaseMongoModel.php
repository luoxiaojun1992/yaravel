<?php

namespace App\Domains\Base\Repositories\Models;

use App\Domains\Base\Repositories\Models\Traits\ModelEvents;
use App\Domains\Base\Repositories\Models\Traits\ShortUuid;
use App\Domains\Common\Support\Auth;
use App\Support\Traits\PhpSapi;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

/**
 * Class BaseMongoModel
 *
 * @property string $jing_uuid
 * @package App\Models
 */
class BaseMongoModel extends Model
{
    use PhpSapi;
    use ModelEvents;
    use ShortUuid;
    use SoftDeletes;

    public $cacheKey = '';
    protected $connection = 'mongodb';
    public $primaryKey = '_id';
    protected $perPage = 15;
    protected $checkMid = false;

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

    const ENTITY_NOT_EXIST = "ENTITY_NOT_EXIST";

    protected $dates = [self::CREATED_AT, self::UPDATED_AT];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        if (!in_array(config('uuid.default_uuid_column'), $this->fillable)) {
            array_push($this->fillable, config('uuid.default_uuid_column'));
        }

        $this->setTablePrefix();

        parent::__construct($attributes);
    }

    private function setTablePrefix()
    {
        $connectionName = $this->getConnectionName();
        $prefix = config('database.connections.' . $connectionName);
        $this->setTable($prefix . $this->getTable());
    }

    public function getEntityCacheKey()
    {
        if (!$this->cacheKey) {
            $this->cacheKey = strtoupper($this->getTable()) . '_ENTITY_';
        }

        return $this->cacheKey . $this->getKey();
    }

    public static function loadFromCacheById($id)
    {
        $model = new static();
        $model->setAttribute($model->getKeyName(), $id);

        $data = \Cache::get($model->getEntityCacheKey());
        if (!$data) {
            $model = self::findOrFail($id);
            if ($model->id) {
                \Cache::add($model->getEntityCacheKey(), json_encode($model->getAttributes()), 86400);
            } else {
                \Cache::add($model->getEntityCacheKey(), static::ENTITY_NOT_EXIST, 86400);
            }
        } elseif ($data == static::ENTITY_NOT_EXIST) {
            throw (new ModelNotFoundException())->setModel(
                get_class($model), $id
            );
        } else {
            $model->fill(json_decode($data, true));
        }

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function __call($method, $parameters)
    {
        $result = parent::__call($method, $parameters);

        if (in_array($method, array('find', 'findOrFail'))) {
            $result->checkPermission();
        }

        return $result;
    }

    /**
     * @throws \Exception
     */
    public function checkPermission()
    {
        if (!$this->isCli() && $this->checkMid) {
            if (Auth::mid() != $this->mid) {
                throw new \Exception('You don\'t have permission to access the object!');
            }
        }
    }
}
