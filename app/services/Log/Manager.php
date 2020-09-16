<?php

namespace App\Services\Log;

use App\Support\Manager as AbstractManager;

class Manager extends AbstractManager
{
    /** @var string */
    protected $defaultDriver;

    /**
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->defaultDriver;
    }

    /**
     * @param string $defaultDriver
     * @return $this
     */
    public function setDefaultDriver($defaultDriver)
    {
        $this->defaultDriver = $defaultDriver;
        return $this;
    }

    /**
     * @param string|null $channelName
     * @return Logger
     */
    public function channel($channelName = null)
    {
        return $this->driver($channelName);
    }

    /**
     * @param string $channelName
     * @return bool
     */
    public function hasChannel($channelName)
    {
        return $this->hasDriver($channelName);
    }

    /**
     * @return Logger[]
     */
    public function channels()
    {
        return $this->getDrivers();
    }
}
