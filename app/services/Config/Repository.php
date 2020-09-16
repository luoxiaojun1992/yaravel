<?php

namespace App\Services\Config;

use App\Support\Arr;
use App\Support\Collection;

class Repository extends Collection
{
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->offsetGet($key);
        }

        return value($default);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return Arr::has($this->items, $key);
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return Arr::get($this->items, $key);
    }

    /**
     * Set the item at a given offset.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            Arr::set($this->items, $key, $value);
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param mixed $key
     */
    public function offsetUnset($key)
    {
        Arr::forget($this->items, $key);
    }
}
