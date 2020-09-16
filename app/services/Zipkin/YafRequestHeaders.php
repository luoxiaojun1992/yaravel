<?php

namespace App\Services\Zipkin;

use App\Services\Http\Request;
use Zipkin\Propagation\Getter;
use Zipkin\Propagation\Setter;

final class YafRequestHeaders implements Getter, Setter
{
    /**
     * {@inheritdoc}
     *
     * @param Request $carrier
     */
    public function get($carrier, $key)
    {
        return $carrier->header($key);
    }

    /**
     * {@inheritdoc}
     *
     * @param Request $carrier
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function put(&$carrier, $key, $value)
    {
        $carrier->addHeader($key, $value);
    }
}
