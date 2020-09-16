<?php

namespace App\Domains\Base\Logics\Traits;

use App\Domains\Base\Repositories\BaseRepository;

trait Curd
{
    protected $singleRepositoryClass;

    protected $singleRepository;

    /**
     * @return BaseRepository
     */
    protected function getSingleRepository()
    {
        if (is_null($this->singleRepository)) {
            $this->singleRepository = di($this->singleRepositoryClass);
        }

        return $this->singleRepository;
    }

    /**
     *  List
     *
     * @param int|null $page
     * @param string|null $sort
     * @param int $mid
     * @param string $query
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @throws \Exception
     */
    public function commonGetList($page, $sort, $mid, $query)
    {
        if ($query) {
            $query = json_decode($query, true);
        }
        return $this->getSingleRepository()->getList($page, $sort, $mid, $query);
    }

    /**
     * Create
     *
     * @param int $mid
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function commonCreate($mid, $data)
    {
        $data['mid'] = $mid;
        return $this->getSingleRepository()->addOne($data);
    }

    /**
     * Update
     *
     * @param string $id
     * @param int $mid
     * @param array $data
     * @throws \Exception
     * @return array
     */
    public function commonUpdate($id, $mid, $data)
    {
        return $this->getSingleRepository()->updateByIdAndMid($id, $mid, $data);
    }

    /**
     * Delete
     *
     * @param string $id
     * @param int $mid
     * @return bool|mixed|null
     * @throws \Exception
     */
    public function commonDelete($id, $mid)
    {
        return $this->getSingleRepository()->removeOne($id, $mid);
    }

    /**
     * View
     *
     * @param string $id
     * @param int $mid
     * @return bool|mixed|null
     * @throws \Exception
     */
    public function commonView($id, $mid)
    {
        return $this->getSingleRepository()->view($mid, $id)->toArray();
    }
}
