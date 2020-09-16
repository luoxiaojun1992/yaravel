<?php

namespace App\Domains\Base\Repositories\ShardingRepositories;

use App\Domains\Base\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseMidShardingRepository
 *
 * @method null|Model getModel(int $mid)
 * @method null|Builder getQuery(int $mid)
 * @method string getTable(int $mid)
 * @package App\Domains\Base\ShardingRepositories
 */
class BaseMidShardingRepository extends BaseRepository
{
    /**
     * Get Table Name Suffix
     *
     * @return string
     * @throws \Exception
     */
    public function getTableSuffix()
    {
        if (func_num_args() <= 0) {
            throw new \Exception('Invalid argument: missing mid');
        }

        $mid = func_get_args()[0];

        return '_' . $mid;
    }

    public function doQuery($mid, $callback)
    {
        $table = $this->getTable($mid);
        $query = $this->getQuery($mid);
        $result = call_user_func_array($callback, [$query]);
        if ($result instanceof Collection) {
            foreach ($result as $item) {
                if ($item instanceof Model) {
                    $item->setTable($table);
                }
            }
        } elseif ($result instanceof Model) {
            $result->setTable($table);
        }

        return $result;
    }
}
