<?php

namespace Tests\Fixtures;

class Behaviors extends \Illuminate\Support\Collection
{
    /**
     * Add a behavior
     *
     * @param  $method
     * @param  array  $args
     * @param  null   $return
     * @param  int    $times
     * @return $this
     */
    public function addOne($method, $args = [], $return = null, $times = 0)
    {
        $behavior = [
            'method' => $method,
        ];
        if (count($args) > 0) {
            $behavior['args'] = $args;
        }
        if (!is_null($return)) {
            $behavior['return'] = $return;
        }
        if ($times > 0) {
            $behavior['times'] = $times;
        }
        return $this->push($behavior);
    }
}
