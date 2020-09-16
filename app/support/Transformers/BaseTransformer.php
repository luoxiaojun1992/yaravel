<?php

namespace App\Support\Transformers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class BaseTransformer
{
    protected $data;
    protected $hasData = false;
    protected $specTransform;

    /**
     * BaseTransformer constructor.
     * @param null $specTransform
     */
    public function __construct(
        $specTransform = null
    )
    {
        $this->specTransform = $specTransform;
    }

    /**
     * @param Model $model
     * @return array
     */
    protected function modelSerialize($model)
    {
        return $model->toArray();
    }

    /**
     * @param LengthAwarePaginator $paginator
     * @return array
     */
    protected function paginationSerialize($paginator)
    {
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $links = [];
        if ($currentPage > 1) {
            $links['previous'] = $paginator->url($currentPage - 1);
        } else {
            $links['previous'] = null;
        }
        if ($currentPage < $lastPage) {
            $links['next'] = $paginator->url($currentPage + 1);
        } else {
            $links['next'] = null;
        }
        return [
            'items' => $paginator->items(),
            '_meta' => [
                'totalCount' => $paginator->total(),
                'pageCount' => $lastPage,
                'currentPage' => $currentPage,
                'perPage' => $paginator->perPage(),
            ],
            '_links' => $links,
        ];
    }

    public function transform($data)
    {
        return $data;
    }

    public function present()
    {
        if (!is_null($this->specTransform)) {
            $transformMethod = 'transform' . ucfirst($this->specTransform);
            if (method_exists($this, $transformMethod)) {
                $this->data = $this->{$transformMethod}($this->data);
            }
        } else {
            $this->data = $this->transform($this->data);
        }

        return $this->data;
    }

    /**
     * @return bool
     */
    public function hasData()
    {
        return $this->hasData;
    }

    /**
     * @param mixed $data
     * @param bool $paginationSerialize
     * @param bool $modelSerialize
     * @return $this
     */
    public function setData($data, $paginationSerialize = true, $modelSerialize = false)
    {
        $this->data = $data;

        if ($paginationSerialize) {
            if ($this->data instanceof LengthAwarePaginator) {
                $this->data = $this->paginationSerialize($this->data);
            }
        }
        if ($modelSerialize) {
            if ($this->data instanceof Model) {
                $this->data = $this->modelSerialize($this->data);
            }
        }

        $this->hasData = true;

        return $this;
    }

    /**
     * @return null
     */
    public function getSpecTransform()
    {
        return $this->specTransform;
    }
}
