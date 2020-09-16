<?php

/**
 * Class Elasticsearch
 *
 * @method static Elasticsearch\Client connection(?string $name = null)
 * @method static int countConnections()
 * @method static int maxConnections()
 * @method static bool hasConnection(string $name)
 * @method static void removeConnection(mixed $name = null)
 */
class Elasticsearch extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'elasticsearch';
    }
}
