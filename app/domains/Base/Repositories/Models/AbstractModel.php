<?php

namespace App\Domains\Base\Repositories\Models;

use App\Domains\Base\Repositories\Models\Traits\ModelEvents;
use App\Domains\Common\Support\Auth;
use App\Support\Traits\PhpSapi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class AbstractModel
 *
 * @package App\Models
 */
abstract class AbstractModel extends Model
{
    use PhpSapi;
    use ModelEvents;

    public $cacheKey = '';
    protected $perPage = 15;
    protected $checkMid = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    const ENTITY_NOT_EXIST = "ENTITY_NOT_EXIST";

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
            /** @var static $model */
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
