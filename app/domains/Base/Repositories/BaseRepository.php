<?php

namespace App\Domains\Base\Repositories;

use App\Consts\Errors;
use App\Domains\Base\Repositories\Models\Traits\ModelEvents;
use App\Support\Traits\PhpSapi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\UTCDateTime;
use App\Exceptions\HttpException;

class BaseRepository
{
    use PhpSapi;

    //Model Class
    protected $model;

    //Soft delete field, don't set if using laravel soft deleting
    protected $soft_delete_field;

    protected $not_soft_deleted = 0;

    protected $soft_deleted = 1;

    protected $filterable = ['mid'];

    public function __construct()
    {
        if (!in_array('mid', $this->filterable)) {
            array_push($this->filterable, 'mid');
        }

        \Registry::set(static::class, $this);
    }

    /**
     * Get Query Builder
     *
     * @return Builder|null
     */
    public function getQuery()
    {
        $model = call_user_func_array([$this, 'getModel'], func_get_args());
        if ($model) {
            return $model->newQuery();
        }

        return null;
    }

    /**
     * Get Table Name
     *
     * @return string
     */
    public function getTable()
    {
        $model = call_user_func_array([$this, 'getModel'], func_get_args());
        if ($model) {
            return $model->getTable();
        }

        return '';
    }

    /**
     * Get Table Name Suffix
     *
     * @return string
     */
    public function getTableSuffix()
    {
        return '';
    }

    /**
     * Get model instance
     *
     * @return null|Model
     */
    public function getModel()
    {
        $model_class = $this->model;

        if (class_exists($model_class)) {
            /**
             * @var Model $model
             */
            $model = new $model_class();
            $tableSuffix = call_user_func_array([$this, 'getTableSuffix'], func_get_args());
            return $model->setTable($model->getTable() . $tableSuffix);
        }

        return null;
    }

    /**
     * @param $model_class
     */
    public function model($model_class)
    {
        $this->model = $model_class;
    }

    /**
     * Fetch data as an array
     *
     * @param $where
     * @param array $columns
     * @param int $limit
     * @param null|string $orderRaw
     * @param int $offset
     * @param bool $distinct
     * @param string $table_suffix
     * @param bool|null $delete
     * @return array
     */
    public function fetch(
        $where,
        $columns = ['*'],
        $limit = 0,
        $orderRaw = null,
        $offset = 0,
        bool $distinct = false,
        $table_suffix = '',
        $delete = null
    ) {
        $query = $this->getQuery();
        if ($query) {
            $model = $query->getModel();
            $model->setTable($model->getTable() . $table_suffix);

            $query->where($where);

            if (!is_null($this->soft_delete_field)) {
                if ($delete === true) {
                    $query->where([$this->soft_delete_field => $this->soft_deleted]);
                } elseif ($delete === false) {
                    $query->where([$this->soft_delete_field => $this->not_soft_deleted]);
                }
            }

            if ($limit > 0) {
                $query->offset($offset)->limit($limit);
            }
            if (!empty($orderRaw)) {
                $query->orderByRaw($orderRaw);
            }
            if ($distinct) {
                $query->distinct();
            }
            return $query->get($columns)->toArray();
        }

        return [];
    }

    /**
     * Fetch one data as an array
     *
     * @param $where
     * @param array $columns
     * @param string $table_suffix
     * @param bool|null $delete
     * @param null $orderColumn
     * @param null $orderBy
     * @param null $orderRaw
     * @return array|null
     */
    public function fetchOne(
        $where,
        $columns = ['*'],
        $table_suffix = '',
        $delete = null,
        $orderColumn=null,
        $orderBy = null,
        $orderRaw = null
    )
    {
        if (!empty($orderColumn) && !empty($orderBy)) {
            if (empty($orderRaw)) {
                $orderRaw = $orderColumn . ' ' . $orderBy;
            } else {
                $orderRaw .= (', ' . $orderColumn . ' ' . $orderBy);
            }
        }

        $result = $this->fetch($where, $columns, 1, $orderRaw, 0, false, $table_suffix, $delete);
        if (count($result) > 0) {
            return $result[0];
        }

        return null;
    }


    /**
     * Get a column of a row
     *
     * @param  $where
     * @param  $scalar_name
     * @return mixed
     */
    public function getScalar($where, $scalar_name)
    {
        $query = $this->getQuery();
        if ($query) {
            return $query->where($where)->value($scalar_name);
        }

        return null;
    }

    /**
     * Increment a column
     *
     * @param  $where
     * @param  $column
     * @param  $amount
     * @return int
     */
    public function incr($where, $column, $amount)
    {
        $query = $this->getQuery();
        if ($query) {
            return $query->where($where)->increment($column, $amount);
        }

        return 0;
    }

    /**
     * Decrement a column
     *
     * @param  $where
     * @param  $column
     * @param  $amount
     * @return int
     */
    public function decr($where, $column, $amount)
    {
        $query = $this->getQuery();
        if ($query) {
            return $query->where($where)->decrement($column, $amount);
        }

        return 0;
    }

    /**
     * Update columns
     *
     * @param  $where
     * @param  $values
     * @param  array  $increments
     * @param  int    $limit
     * @return int
     */
    public function update($where, $values, $increments = [], $limit = 0)
    {
        $query = $this->getQuery();
        if ($query) {
            if (count($increments) > 0) {
                foreach ($increments as $column => $amount) {
                    if ($amount == 0) {
                        continue;
                    }
                    //Escape column
                    $wrapped_column = $this->getQuery()->getQuery()->getGrammar()->wrap($column);
                    $values[$column] = DB::raw($wrapped_column . ($amount > 0 ? ' + ' : ' - ') . $amount);
                }
            }
            if ($limit > 0) {
                $query->limit($limit);
            }
            return $query->where($where)->update($values);
        }

        return 0;
    }

    /**
     * Create a data
     *
     * @param  $values
     * @return array
     */
    public function create($values) : array
    {
        $model = $this->getModel();
        if ($model) {
            $model->fill($values);
            if ($model->save()) {
                return $model->toArray();
            }
        }

        return [];
    }

    /**
     * Delete rows
     *
     * @param $where
     * @param int $limit
     * @return int|mixed
     */
    public function delete($where, int $limit = 0)
    {
        $query = $this->getQuery();
        if ($query) {
            if ($limit > 0) {
                $query->limit($limit);
            }

            $query->where($where);
            if ($this->soft_delete_field) {
                $query->where(
                    [
                        $this->soft_delete_field => $this->not_soft_deleted,
                    ]
                );
                return $query->update(
                    [
                        $this->soft_delete_field => $this->soft_deleted,
                    ]
                );
            } else {
                return $query->delete();
            }
        }

        return 0;
    }

    /**
     * Get paginated list
     *
     * @param string|null $sort
     * @param int|null $page
     * @param int $mid
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @throws \Exception
     */
    public function getList($page, $sort, $mid, $params)
    {
        $model = $this->getModel($mid);
        if ($model instanceof \Jenssegers\Mongodb\Eloquent\Model) {
            $query = $this->mongoSearch($mid, $params);
        } else {
            $query = $this->mysqlSearch($mid, $params);
        }
        if ($sort) {
            $direction = substr($sort, 0, 1) === '-' ? 'desc' : 'asc';
            if ($direction === 'desc') {
                $sortField = substr($sort, 1);
            } else {
                $sortField = $sort;
            }
            $query->orderBy($sortField, $direction);
        }
        return $query->paginate(
            null,
            ['*'],
            'page',
            $page ? : null
        );
    }

    /**
     * Search from mysql
     *
     * @param int $mid
     * @param array $params
     * @return Builder|null
     * @throws \Exception
     */
    public function mysqlSearch($mid, $params)
    {
        $query = $this->getQuery($mid);
        $params = isset($params['filter']) && is_array($params['filter']) ? $params['filter'] : array();
        if ((!isset($params['mid']) || !$params['mid']) && !$this->isCli()) {
            if ($mid) {
                $params['mid'] = $mid;
            }
        }
        $filterable = $this->filterable;
        if (in_array('*', $filterable)) {
            $filterable = array_merge($filterable, array_keys($params));
        }

        $operatorMapping = array(
            "gt" => ">",
            "lt" => "<",
            "gte" => ">=",
            "lte" => "<=",
        );
        foreach($filterable as $attributesCode) {
            if ($attributesCode === '*') {
                continue;
            }

            if (isset($params[$attributesCode])) {
                if (is_array($params[$attributesCode])) {
                    foreach($params[$attributesCode] as $p => $v) {
                        if (isset($operatorMapping[$p])) {
                            $p = $operatorMapping[$p];
                        }
                        if ($p === 'like') {
                            $v = '%' . $v . '%';
                        }
                        $query->where($attributesCode, $p, $v);
                    }
                } elseif ($params[$attributesCode] == 'have_value') {
                    $query->where($attributesCode, 'exists', true);
                    $query->whereNotNull($attributesCode);
                } elseif ($params[$attributesCode] == 'no_value') {
                    $query->where($attributesCode, 'exists', false);
                    $query->whereNull($attributesCode);
                } else {
                    $query->where($attributesCode, '=', $params[$attributesCode]);
                }
            }
        }

        return $query;

    }

    /**
     * Search from mongodb
     *
     * @param int $mid
     * @param array $params
     * @return Builder|null
     * @throws \Exception
     */
    public function mongoSearch($mid, $params)
    {
        $query = $this->getQuery($mid);
        $params = isset($params['filter']) && is_array($params['filter']) ? $params['filter'] : array();
        if ((!isset($params['mid']) || !$params['mid']) && !$this->isCli()) {
            if ($mid) {
                $params['mid'] = $mid;
            }
        }
        $filterable = $this->filterable;
        if (in_array('*', $filterable)) {
            $filterable = array_merge($filterable, array_keys($params));
        }

        $operatorMapping = array(
            "gt" => ">",
            "lt" => "<",
            "gte" => ">=",
            "lte" => "<=",
        );
        foreach($filterable as $attributesCode) {
            if ($attributesCode === '*') {
                continue;
            }

            if (isset($params[$attributesCode])) {
                if (is_array($params[$attributesCode])) {
                    foreach($params[$attributesCode] as $p => $v) {
                        if (isset($operatorMapping[$p])) {
                            $p = $operatorMapping[$p];
                        }
                        if (in_array($attributesCode, $query->getModel()->getDates())) {
                            $v = new UTCDateTime($v * 1000);
                        }
                        if ($p === 'like') {
                            $v = '%' . $v . '%';
                        }
                        $query->where($attributesCode, $p, $v);
                    }
                } elseif ($params[$attributesCode] == 'have_value') {
                    $query->where($attributesCode, 'exists', true);
                    $query->whereNotNull($attributesCode);
                } elseif ($params[$attributesCode] == 'no_value') {
                    $query->where($attributesCode, 'exists', false);
                    $query->whereNull($attributesCode);
                } else {
                    $query->where($attributesCode, '=', $params[$attributesCode]);
                }
            }
        }

        return $query;
    }

    /**
     * Fetch by uuid
     *
     * @param int $mid
     * @param string $id
     * @return Model
     */
    public function view($mid, $id)
    {
        return $this->getQuery($mid)->where([
            'mid' => $mid,
        ])->shortUuid($id);
    }

    /**
     * Add a data
     *
     * @param array $values
     * @return array
     * @throws \Exception
     */
    public function addOne($values)
    {
        /** @var Model|ModelEvents $model */
        $model = $this->getModel();
        $model->fill($values);
        if (!$model->save()) {
            $code = Errors::ERROR;
            if ($model->hasErrors()) {
                $errors = $this->serializeModelErrors($model->getErrors());
            } else {
                $errors = 'Failed to create the object for unknown reason';
            }

            throw new HttpException(200, $errors, null, [], $code);
        }

        return $model->toArray();
    }

    /**
     * Remove a data
     *
     * @param string $id
     * @param int $mid
     * @return bool
     * @throws \Exception
     */
    public function removeOne($id, $mid)
    {
        $model = self::view($mid, $id);

        if (!$model->delete()) {
            throw new HttpException(200, 'Failed to delete the object for unknown reason', null, [], Errors::ERROR);
        }

        return true;
    }

    /**
     * Update a data by id and mid
     *
     * @param string $id
     * @param int $mid
     * @param array $values
     * @throws \Exception
     * @return array
     */
    public function updateByIdAndMid($id, $mid, $values)
    {
        /** @var Model|ModelEvents $model */
        $model = $this->getQuery($mid)->where([
            'mid' => $mid,
        ])->shortUuid($id);

        $model->fill($values);
        //即使数据没有改变，save方法也是返回true
        if (!$model->save()) {
            $code = Errors::ERROR;
            if ($model->hasErrors()) {
                $errors = $this->serializeModelErrors($model->getErrors());
            } else {
                $errors = 'Failed to update the object for unknown reason';
            }

            throw new HttpException(200, $errors, null, [], $code);
        }

        return $model->toArray();
    }

    protected function shardSuffix($shard_field, $shard_num)
    {
        return str_pad(($shard_field % $shard_num) + 1, 3, '0', STR_PAD_LEFT);
    }

    protected function serializeModelErrors($errors)
    {
        $errorStr = '';
        foreach ($errors as $name => $error) {
            if (!empty($error)) {
                $errorStr .= reset($error) . PHP_EOL . ' ';
            }
        }

        return $errorStr;
    }
}
