<?php

namespace App\Support\Lock;

class RedisLock extends \Illuminate\Cache\RedisLock
{
    public function acquire()
    {
        if ($this->seconds > 0) {
            return $this->redis->set($this->name, 1, 'EX', $this->seconds, 'NX') === true;
        }

        return parent::acquire();
    }

    public function delay($ttl)
    {
        return $this->redis->expire($this->name, $ttl);
    }

    public function ttl()
    {
        return $this->redis->ttl($this->name);
    }
}
