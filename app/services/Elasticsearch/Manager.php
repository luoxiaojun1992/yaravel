<?php

namespace App\Services\Elasticsearch;

class Manager extends \Cviebrock\LaravelElasticsearch\Manager
{
    /**
     * @return int
     */
    public function countConnections()
    {
        return count($this->connections);
    }

    /**
     * @return int
     */
    public function maxConnections()
    {
        return config('elasticsearch.maxConnection', 10);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasConnection($name)
    {
        return isset($this->connections[$name]);
    }

    /**
     * @param mixed $name
     */
    public function removeConnection($name = null)
    {
        $name = $name ?: $this->getDefaultConnection();

        if (isset($this->connections[$name])) {
            unset($this->connections[$name]);
        }
    }
}
