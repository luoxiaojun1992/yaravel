<?php

namespace App\Domains\Base\Logics;

use App\Domains\Base\Logics\Traits\Curd;

class BaseLogic
{
    use Curd;

    public function __construct()
    {
        \Registry::set(static::class, $this);
    }

    /**
     * 获取列表的返回数据
     * @param int $total 总数据
     * @param int $current_page 当前页面
     * @param int $size 每页数量
     * @return array
     */
    public static function getListParamsFroMeta($total, $current_page = 1, $size = 20)
    {
        $max_page_num = ceil($total / $size);
        return [
            'totalCount' => $total,
            'pageCount' => $max_page_num,
            'currentPage' => ($current_page > $max_page_num) ? $max_page_num : $current_page,
            'perPage' => $size
        ];
    }
}
