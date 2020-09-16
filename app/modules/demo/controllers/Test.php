<?php

/*
 * This file is part of the overtrue/yaf-skeleton.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * class TestController.
 *
 * @author overtrue <i@overtrue.me>
 */
class TestController extends BaseController
{

    public function handle()
    {
        //
    }

    public function testAction()
    {
        $this->handleResponse(['foo' => 'bar']);
    }
}
