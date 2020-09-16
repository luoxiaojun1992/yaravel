<?php

/**
 * Class View
 *
 * @method static string render(string $name, array $data = array())
 */
class View extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'services.view';
    }
}
