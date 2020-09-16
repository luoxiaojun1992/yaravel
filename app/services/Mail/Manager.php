<?php

namespace App\Services\Mail;

class Manager extends \App\Support\Manager
{
    /** @var string */
    protected $defaultChannel;

    protected $channelsConfig;

    public function getDefaultDriver()
    {
        return $this->defaultChannel;
    }

    /**
     * @param string $defaultChannel
     * @return $this
     */
    public function setDefaultChannel(string $defaultChannel)
    {
        $this->defaultChannel = $defaultChannel;
        return $this;
    }

    public function getDefaultChannel()
    {
        return $this->getDefaultDriver();
    }

    /**
     * @param $channelsConfig
     * @return $this
     */
    public function setChannelsConfig($channelsConfig)
    {
        $this->channelsConfig = $channelsConfig;
        return $this;
    }

    /**
     * @param $channel
     * @param $channelConfig
     * @return $this
     */
    public function addChannelConfig($channel, $channelConfig)
    {
        $this->channelsConfig[$channel] = $channelConfig;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getChannelsConfig()
    {
        return $this->channelsConfig;
    }

    protected function createDriver($driver)
    {
        if (!isset($this->channelsConfig[$driver])) {
            throw new \InvalidArgumentException("Driver [$driver] not supported.");
        }

        $channelConfig = $this->channelsConfig[$driver];

        // Create the Transport
        $transport = (new \Swift_SmtpTransport(
            $channelConfig['host'] ?? 'localhost',
            $channelConfig['port'] ?? 25,
            $channelConfig['encryption'] ?? null
        ))->setUsername($channelConfig['username'])->setPassword($channelConfig['password']);

        // Create the Mailer using your created Transport
        return (new \Swift_Mailer($transport));
    }

    public function channels()
    {
        return $this->getDrivers();
    }

    public function channel($channel = null)
    {
        return $this->driver($channel);
    }
}
