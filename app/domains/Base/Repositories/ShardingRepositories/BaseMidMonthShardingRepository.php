<?php

namespace App\Domains\Base\Repositories\ShardingRepositories;

use App\Domains\Base\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseMidMonthShardingRepository
 *
 * @method null|Model getModel(int $mid, string $month = null)
 * @method null|Builder getQuery(int $mid, string $month = null)
 * @method string getTable(int $mid, string $month = null)
 * @package App\Domains\Base\ShardingRepositories
 */
class BaseMidMonthShardingRepository extends BaseRepository
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

        $args = func_get_args();

        $mid = $args[0];

        $month = $args[1] ?? Carbon::now()->format('Y_m');

        return '_' . $mid . '_' . $month;
    }

    public function doQuery($mid, $callback, $month = null)
    {
        $table = $this->getTable(...(isset($month) ? [$mid, $month] : [$mid]));
        $query = $this->getQuery(...(isset($month) ? [$mid, $month] : [$mid]));
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
