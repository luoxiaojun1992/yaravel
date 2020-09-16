<?php
//
///*
// * This file is part of the overtrue/yaf-skeleton.
// *
// * (c) overtrue <i@overtrue.me>
// *
// * This source file is subject to the MIT license that is bundled
// * with this source code in the file LICENSE.
// */
//
///**
// * class TestController.
// *
// * @author overtrue <i@overtrue.me>
// */
//class TestController extends BaseController
//{
//    /**
//     * @return \Psr\Http\Message\ResponseInterface
//     */
//    public function handle()
//    {
//        $config = config('app');
//
//        return view('welcome', $config);
//    }
//
//    public function jsonAction()
//    {
//        $this->handleResponse(['foo' => 'bar']);
//    }
//
//    public function dbAction()
//    {
//        $this->handleResponse(\App\Models\Test::query()->limit(1)->first()->toArray());
//    }
//
//    public function paginationAction()
//    {
//        $this->handleResponse(
//            (new \App\Support\Transformers\ActionTransformer(
//                __METHOD__
//            ))->setData(\App\Models\Test::query()->paginate())
//        );
//    }
//
//    public function apiAction()
//    {
//        $this->apiResponse(
//            0,
//            '',
//            \App\Models\Test::query()->paginate(),
//            200,
//            [],
//            new \App\Support\Transformers\ActionTransformer(__METHOD__)
//        );
//    }
//
//    public function apifailAction()
//    {
//        $this->apiFail();
//    }
//
//    /**
//     * 正常测试结果：返回method名
//     */
//    public function diAction()
//    {
//        /** @var \App\Support\Transformers\ActionTransformer $transformer */
//        $transformer = di(
//            \App\Support\Transformers\ActionTransformer::class,
//            [__METHOD__]
//        );
//
//        $this->handleResponse([
//            'action' => $transformer->getSpecTransform()
//        ]);
//    }
//
//    /**
//     * 正常测试结果：返回method名
//     */
//    public function laravelDiAction()
//    {
//        /** @var \App\Support\Transformers\ActionTransformer $transformer */
//        $transformer = laravel_di()->make(
//            \App\Support\Transformers\ActionTransformer::class,
//            ['specTransform' => __METHOD__]
//        );
//
//        $this->handleResponse([
//            'action' => $transformer->getSpecTransform()
//        ]);
//    }
//
//    /**
//     * 正常测试结果：hash值不同
//     */
//    public function laravelDiMakeAction()
//    {
//        $transformer1 = laravel_di()->make(
//            \App\Support\Transformers\ActionTransformer::class,
//            ['specTransform' => __METHOD__]
//        );
//        $transformer2 = laravel_di()->make(
//            \App\Support\Transformers\ActionTransformer::class,
//            ['specTransform' => __METHOD__]
//        );
//        $this->handleResponse([
//            'transformer1' => spl_object_hash($transformer1),
//            'transformer2' => spl_object_hash($transformer2),
//        ]);
//    }
//
//    /**
//     * 正常测试结果：hash值不同
//     */
//    public function diLaravelDiMakeAction()
//    {
//        $transformer1 = laravel_di()->make(
//            \App\Support\Transformers\ActionTransformer::class,
//            ['specTransform' => __METHOD__]
//        );
//        $transformer2 = di(
//            \App\Support\Transformers\ActionTransformer::class,
//            [__METHOD__]
//        );
//        $this->handleResponse([
//            'transformer1' => spl_object_hash($transformer1),
//            'transformer2' => spl_object_hash($transformer2),
//        ]);
//    }
//
//    /**
//     * 正常测试结果：hash值相同
//     */
//    public function diLaravelDiInstanceAction()
//    {
//        $transformer1 = laravel_di()->make(
//            \App\Support\Transformers\ActionTransformer::class,
//            ['specTransform' => __METHOD__]
//        );
//        laravel_di()->instance(
//            \App\Support\Transformers\ActionTransformer::class,
//            $transformer1
//        );
//        $transformer2 = di(
//            \App\Support\Transformers\ActionTransformer::class,
//            [__METHOD__]
//        );
//        $this->handleResponse([
//            'transformer1' => spl_object_hash($transformer1),
//            'transformer2' => spl_object_hash($transformer2),
//        ]);
//    }
//
//    /**
//     * 正常测试结果：返回method名
//     */
//    public function compatibleLaravelDiAction()
//    {
//        /** @var \App\Support\Transformers\ActionTransformer $transformer */
//        $transformer = app(
//            \App\Support\Transformers\ActionTransformer::class,
//            ['specTransform' => __METHOD__]
//        );
//
//        $this->handleResponse([
//            'action' => $transformer->getSpecTransform()
//        ]);
//    }
//
//    /**
//     * 正常测试结果：返回failed对应message或中文翻译，取决于app.locale配置
//     */
//    public function translateAction()
//    {
//        $this->handleResponse(
//            ['translation' => \App\Support\Translator::__('auth', 'failed')]
//        );
//    }
//
//    /**
//     * 正常测试结果：发送队列消息(取决于queue driver)，不返回任何内容
//     */
//    public function queueAction()
//    {
//        \App\Jobs\TestJob::dispatch('data');
//    }
//
//    /**
//     * 正常测试结果：返回{"foo":"bar"}
//     */
//    public function redisAction()
//    {
//        $key = 'foo';
//        $redis = RedisFacade::connection('default');
//        $redis->set($key, 'bar');
//        $this->handleResponse([$key => $redis->get($key)]);
//    }
//
//    /**
//     * 正常测试结果：记录日志(取决于日志配置)，不返回任何内容
//     */
//    public function logAction()
//    {
//        Log::info('test');
//    }
//
//    /**
//     * 正常测试结果：返回{"res":true}
//     */
//    public function validateAction()
//    {
//        $res = Validator::make(['foo' => 'bar'], ['foo' => 'string'])->fails();
//        $this->handleResponse(json(['res' => !$res]));
//    }
//
//    /**
//     * 正常测试结果：返回{"value":"bar"}
//     */
//    public function sessionAction()
//    {
//        Session::set('foo', 'bar');
//        $this->handleResponse(json(['value' => Session::get('foo')]));
//    }
//
//    /**
//     * 正常测试结果：返回{code: 1, msg: "test exception", message: "test exception"}
//     *
//     * @throws Exception
//     */
//    public function exceptionAction()
//    {
//        $this->testException();
//    }
//
//    /**
//     * @throws Exception
//     */
//    protected function testException()
//    {
//        throw new \Exception('test exception');
//    }
//
//    /**
//     * 正常测试结果：全为true
//     */
//    public function arrAction()
//    {
//        $arr1 = ['a' => ['b' => ['c' => 'd']]];
//        \App\Support\Arr::forget($arr1, 'a.b.c');
//        $test1 = ($arr1 === ['a' => ['b' => []]]);
//
//        $arr2 = ['a' => ['b' => ['c' => 'd']]];
//        \App\Support\Arr::forget($arr2, 'a.b');
//        $test2 = ($arr2 === ['a' => []]);
//
//        $arr3 = ['a' => ['b' => ['c' => 'd']]];
//        \App\Support\Arr::forget($arr3, 'a.d');
//        $test3 = ($arr3 === ['a' => ['b' => ['c' => 'd']]]);
//
//        $arr19 = ['a' => ['b' => ['c' => 'd']], 'k' => 'l'];
//        \App\Support\Arr::forget($arr19, ['a.b.c', 'k']);
//        $test19 = ($arr19 === ['a' => ['b' => []]]);
//
//        $arr4 = ['a' => ['b' => ['c' => 'd']]];
//        \App\Support\Arr::set($arr4, 'a.b.e', 'd');
//        $test4 = ($arr4 === ['a' => ['b' => ['c' => 'd', 'e' => 'd']]]);
//
//        $arr5 = ['a' => ['b' => ['c' => 'd']]];
//        \App\Support\Arr::set($arr5, 'a.e', 'd');
//        $test5 = ($arr5 === ['a' => ['b' => ['c' => 'd'], 'e' => 'd']]);
//
//        $arr6 = ['a' => ['b' => ['c' => 'd']]];
//        \App\Support\Arr::set($arr6, 'a.b', 'd');
//        $test6 = ($arr6 === ['a' => ['b' => 'd']]);
//
//        $arr7 = ['a' => ['b' => ['c' => 'd']]];
//        \App\Support\Arr::set($arr7, 'a.b.c', 'e');
//        $test7 = ($arr7 === ['a' => ['b' => ['c' => 'e']]]);
//
//        $arr16 = ['a' => ['b' => ['c' => 'd']]];
//        \App\Support\Arr::set($arr16, 'a.b.c.f', 'e');
//        $test16 = ($arr16 === ['a' => ['b' => ['c' => 'd']]]);
//
//        $arr17 = ['a' => ['b' => ['c' => 'd']]];
//        \App\Support\Arr::set($arr17, 'a.b.f.g', 'e');
//        $test17 = ($arr17 === ['a' => ['b' => ['c' => 'd', 'f' => ['g' => 'e']]]]);
//
//        $arr18 = ['a' => ['b' => ['c' => 'd']]];
//        \App\Support\Arr::set($arr18, 'k', 'l');
//        $test18 = ($arr18 === ['a' => ['b' => ['c' => 'd']], 'k' => 'l']);
//
//        $arr8 = ['a' => ['b' => ['c' => 'd']]];
//        $res8 = \App\Support\Arr::get($arr8, 'a.b');
//        $test8 = ($res8 === ['c' => 'd']);
//
//        $arr9 = ['a' => ['b' => ['c' => 'd']]];
//        $res9 = \App\Support\Arr::get($arr9, 'a.b.c');
//        $test9 = ($res9 === 'd');
//
//        $arr10 = ['a' => ['b' => ['c' => 'd']]];
//        $res10 = \App\Support\Arr::get($arr10, 'a.k', 'e');
//        $test10 = ($res10 === 'e');
//
//        $arr11 = ['a' => ['b' => ['c' => 'd']]];
//        $res11 = \App\Support\Arr::get($arr11, 'a.b.k', 'e');
//        $test11 = ($res11 === 'e');
//
//        $arr12 = ['a' => ['b' => ['c' => 'd']]];
//        $res12 = \App\Support\Arr::has($arr12, 'a.b.c');
//        $test12 = ($res12 === true);
//
//        $arr13 = ['a' => ['b' => ['c' => 'd']]];
//        $res13 = \App\Support\Arr::has($arr13, 'a.b');
//        $test13 = ($res13 === true);
//
//        $arr14 = ['a' => ['b' => ['c' => 'd']]];
//        $res14 = \App\Support\Arr::has($arr14, 'a.e');
//        $test14 = ($res14 === false);
//
//        $arr15 = ['a' => ['b' => ['c' => 'd']]];
//        $res15 = \App\Support\Arr::has($arr15, 'a.b.e');
//        $test15 = ($res15 === false);
//
//        $this->handleResponse(json(
//            compact(
//                'test1', 'test2', 'test3', 'test4', 'test5', 'test6',
//                'test7', 'test8', 'test9', 'test10', 'test11', 'test12', 'test13',
//                'test14', 'test15', 'test16', 'test17', 'test18', 'test19'
//            )
//        ));
//    }
//
//    public function requestAction()
//    {
//        $this->handleResponse(json([
//            'all' => Request::all(),
//            'query' => Request::allQuery(),
//            'post' => Request::allPost(),
//            'env' => Request::allEnv(),
//            'server' => Request::allServer(),
//            'cookie' => Request::allCookie(),
//            'files' => Request::allFiles(),
//            'headers' => Request::headers(),
//            'uri' => Request::uri(),
//            'path' => Request::path(),
//            'method' => Request::method(),
//            'raw' => Request::raw(),
//            'ip' => Request::ip(),
//            'ipv6' => Request::isIpV6(),
//            'module' => Request::module(),
//            'controller' => Request::controller(),
//            'action' => Request::action(),
//            'protocol_version' => Request::protocolVersion(),
//            'client_port' => Request::clientPort(),
//            'lang' => Request::lang(),
//            'time' => Request::time(),
//            'bearer_token' => Request::bearerToken(),
//            'auth_token' => Request::authToken(),
//            'content_type' => Request::header('content-type'),
//            'route' => Request::route(),
//            'is_secure' => Request::isSecureConnection(),
//            'httpHost' => Request::httpHost(),
//            'schema' => Request::schema(),
//            'server_port' => Request::serverPort(),
//            'url' => Request::url(),
//            'query_string' => Request::queryString(),
//            'full_url' => Request::fullUrl(),
//            'controller_action' => Request::controllerAction(),
//        ]));
//    }
//
//    /**
//     * 正常测试结果：全部为true
//     */
//    public function requestHeaderAction()
//    {
//        Request::setHeader('x-test', 'bar');
//        $test1 = Request::header('x-test') === 'bar';
//        $test2 = array_key_exists('X-TEST', Request::headers());
//        Request::addHeader('x-test', 'test');
//        $test3 = Request::header('x-test') === 'test';
//        $test4 = array_key_exists('X-TEST', Request::headers());
//        Request::delHeader('x-test');
//        $test5 = Request::header('x-test') === null;
//        $test6 = !array_key_exists('X-TEST', Request::headers());
//
//        Request::setHeader('x-test', 'bar');
//        $test7 = Request::header('x-test') === 'bar';
//        $test8 = array_key_exists('X-TEST', Request::headers());
//        Request::delHeader('x-test');
//        $test9 = Request::header('x-test') === null;
//        $test10 = !array_key_exists('X-TEST', Request::headers());
//        Request::addHeader('x-test', 'test');
//        $test11 = Request::header('x-test') === 'test';
//        $test12 = array_key_exists('X-TEST', Request::headers());
//        Request::delHeader('x-test');
//        $test13 = Request::header('x-test') === null;
//        $test14 = !array_key_exists('X-TEST', Request::headers());
//
//        Request::addHeader('x-test', 'bar');
//        $test15 = Request::header('x-test') === 'bar';
//        $test16 = array_key_exists('X-TEST', Request::headers());
//        Request::setHeader('x-test', 'test');
//        $test17 = Request::header('x-test') === 'test';
//        $test18 = array_key_exists('X-TEST', Request::headers());
//        Request::delHeader('x-test');
//        $test19 = Request::header('x-test') === null;
//        $test20 = !array_key_exists('X-TEST', Request::headers());
//
//        Request::addHeader('x-test', 'bar');
//        $test21 = Request::header('x-test') === 'bar';
//        $test22 = array_key_exists('X-TEST', Request::headers());
//        Request::delHeader('x-test');
//        $test23 = Request::header('x-test') === null;
//        $test24 = !array_key_exists('X-TEST', Request::headers());
//        Request::setHeader('x-test', 'test');
//        $test25 = Request::header('x-test') === 'test';
//        $test26 = array_key_exists('X-TEST', Request::headers());
//        Request::delHeader('x-test');
//        $test27 = Request::header('x-test') === null;
//        $test28 = !array_key_exists('X-TEST', Request::headers());
//
//        Request::delHeader('USER-AGENT');
//        $test29 = Request::header('USER-AGENT') === null;
//        $test30 = !array_key_exists('USER-AGENT', Request::headers());
//
//        Request::setHeader('USER-AGENT', 'bar');
//        $test31 = Request::header('USER-AGENT') === 'bar';
//        $test32 = array_key_exists('USER-AGENT', Request::headers());
//        Request::addHeader('USER-AGENT', 'test');
//        $test33 = Request::header('USER-AGENT') === 'test';
//        $test34 = array_key_exists('USER-AGENT', Request::headers());
//        $test35 = in_array('bar', Request::headers()['USER-AGENT']);
//        Request::delHeader('USER-AGENT');
//        $test36 = Request::header('USER-AGENT') === null;
//        $test37 = !array_key_exists('USER-AGENT', Request::headers());
//
//        Request::setHeader('USER-AGENT', 'bar');
//        $test38 = Request::header('USER-AGENT') === 'bar';
//        $test39 = array_key_exists('USER-AGENT', Request::headers());
//        Request::delHeader('USER-AGENT');
//        $test40 = Request::header('USER-AGENT') === null;
//        $test41 = !array_key_exists('USER-AGENT', Request::headers());
//        Request::addHeader('USER-AGENT', 'test');
//        $test42 = Request::header('USER-AGENT') === 'test';
//        $test43 = array_key_exists('USER-AGENT', Request::headers());
//        Request::delHeader('USER-AGENT');
//        $test44 = Request::header('USER-AGENT') === null;
//        $test45 = !array_key_exists('USER-AGENT', Request::headers());
//
//        Request::addHeader('USER-AGENT', 'bar');
//        $test46 = Request::header('USER-AGENT') === 'bar';
//        $test47 = array_key_exists('USER-AGENT', Request::headers());
//        Request::delHeader('USER-AGENT');
//        $test48 = Request::header('USER-AGENT') === null;
//        $test49 = !array_key_exists('USER-AGENT', Request::headers());
//        Request::setHeader('USER-AGENT', 'test');
//        $test50 = Request::header('USER-AGENT') === 'test';
//        $test51 = array_key_exists('USER-AGENT', Request::headers());
//        Request::delHeader('USER-AGENT');
//        $test52 = Request::header('USER-AGENT') === null;
//        $test53 = !array_key_exists('USER-AGENT', Request::headers());
//
//        Request::addHeader('USER-AGENT', 'bar');
//        $test54 = Request::header('USER-AGENT') === 'bar';
//        $test55 = array_key_exists('USER-AGENT', Request::headers());
//        Request::setHeader('USER-AGENT', 'test');
//        $test56 = Request::header('USER-AGENT') === 'test';
//        $test57 = array_key_exists('USER-AGENT', Request::headers());
//        Request::delHeader('USER-AGENT');
//        $test58 = Request::header('USER-AGENT') === null;
//        $test59 = !array_key_exists('USER-AGENT', Request::headers());
//
//        Request::setHeader('x-test', 'test');
//        $test60 = Request::header('x-test') === 'test';
//        $test61 = array_key_exists('X-TEST', Request::headers());
//        Request::addHeader('USER-AGENT', 'bar');
//        $test62 = Request::header('USER-AGENT') === 'bar';
//        $test63 = array_key_exists('USER-AGENT', Request::headers());
//        Request::addHeader('USER-AGENT', 'test');
//        $test64 = Request::header('USER-AGENT') === 'test';
//        $test65 = array_key_exists('USER-AGENT', Request::headers());
//        $test66 = in_array('bar', Request::headers()['USER-AGENT']);
//        $test67 = Request::header('x-test') === 'test';
//        $test68 = array_key_exists('X-TEST', Request::headers());
//        Request::delHeader('x-test');
//        $test69 = Request::header('x-test') === null;
//        $test70 = !array_key_exists('X-TEST', Request::headers());
//        Request::delHeader('USER-AGENT');
//        $test71 = Request::header('USER-AGENT') === null;
//        $test72 = !array_key_exists('USER-AGENT', Request::headers());
//
//        $this->handleResponse([
//            'test1' => $test1,
//            'test2' => $test2,
//            'test3' => $test3,
//            'test4' => $test4,
//            'test5' => $test5,
//            'test6' => $test6,
//            'test7' => $test7,
//            'test8' => $test8,
//            'test9' => $test9,
//            'test10' => $test10,
//            'test11' => $test11,
//            'test12' => $test12,
//            'test13' => $test13,
//            'test14' => $test14,
//            'test15' => $test15,
//            'test16' => $test16,
//            'test17' => $test17,
//            'test18' => $test18,
//            'test19' => $test19,
//            'test20' => $test20,
//            'test21' => $test21,
//            'test22' => $test22,
//            'test23' => $test23,
//            'test24' => $test24,
//            'test25' => $test25,
//            'test26' => $test26,
//            'test27' => $test27,
//            'test28' => $test28,
//            'test29' => $test29,
//            'test30' => $test30,
//            'test31' => $test31,
//            'test32' => $test32,
//            'test33' => $test33,
//            'test34' => $test34,
//            'test35' => $test35,
//            'test36' => $test36,
//            'test37' => $test37,
//            'test38' => $test38,
//            'test39' => $test39,
//            'test40' => $test40,
//            'test41' => $test41,
//            'test42' => $test42,
//            'test43' => $test43,
//            'test44' => $test44,
//            'test45' => $test45,
//            'test46' => $test46,
//            'test47' => $test47,
//            'test48' => $test48,
//            'test49' => $test49,
//            'test50' => $test50,
//            'test51' => $test51,
//            'test52' => $test52,
//            'test53' => $test53,
//            'test54' => $test54,
//            'test55' => $test55,
//            'test56' => $test56,
//            'test57' => $test57,
//            'test58' => $test58,
//            'test59' => $test59,
//            'test60' => $test60,
//            'test61' => $test61,
//            'test62' => $test62,
//            'test63' => $test63,
//            'test64' => $test64,
//            'test65' => $test65,
//            'test66' => $test66,
//            'test67' => $test67,
//            'test68' => $test68,
//            'test69' => $test69,
//            'test70' => $test70,
//            'test71' => $test71,
//            'test72' => $test72,
//        ]);
//    }
//
//    /**
//     * 正常测试结果：发送邮件(取决于配置)，不返回任何内容
//     */
//    public function mailAction()
//    {
//        $mailConfig = Config::get('mail');
//        $defaultChannel = $mailConfig['default_channel'];
//        $channelConfig = $mailConfig['channels'][$defaultChannel];
//        $this->handleResponse(Mail::send(
//            (new Swift_Message(
//                'test',
//                'test'
//            ))->setFrom($channelConfig['username'])
//                ->setTo($channelConfig['username'])
//                ->setSender($channelConfig['username'], $channelConfig['username'])
//        ));
//    }
//
//    /**
//     * 正常测试结果：
//     *
//     * {
//     *      test1: "foo",
//     *      test2: false,
//     *      test3: "foo"
//     * }
//     */
//    public function lockAction()
//    {
//        $test2 = null;
//        $test1 = \App\Support\Lock\Lock::get('test', 5, function() use (&$test2) {
//            $test2 = \App\Support\Lock\Lock::get('test', 5, function() {
//                return 'foo';
//            });
//            return 'foo';
//        });
//        $test3 = \App\Support\Lock\Lock::get('test', 5, function() use (&$test2) {
//            return 'foo';
//        });
//
//        $this->handleResponse(
//            [
//                'test1' => $test1,
//                'test2' => $test2,
//                'test3' => $test3,
//            ]
//        );
//    }
//
//    /**
//     * 正常测试结果：ttl大于0
//     */
//    public function lockTTLAction()
//    {
//        $ttl = null;
//        $newTTL = null;
//        \App\Support\Lock\Lock::get('test', 5, function() use (&$ttl, &$newTTL) {
//            $ttl = \App\Support\Lock\Lock::ttl('test');
//            \App\Support\Lock\Lock::delay('test', 5);
//            $newTTL = \App\Support\Lock\Lock::ttl('test');
//            return 'foo';
//        });
//        \App\Support\Lock\Lock::release('test');
//
//        $this->handleResponse([
//            'ttl' => $ttl,
//            'newTTL' => $newTTL,
//        ]);
//    }
//
//    /**
//     * 正常测试结果：{"value1":"bar","value2":null}
//     */
//    public function cacheAction()
//    {
//        Cache::put('foo', 'bar', \Carbon\Carbon::now()->addSeconds(5));
//        $value1 = Cache::get('foo');
//        Cache::forget('foo');
//        $value2 = Cache::get('foo');
//        $this->handleResponse([
//            'value1' => $value1,
//            'value2' => $value2
//        ]);
//    }
//
//    /**
//     * 正常测试结果：
//     * {
//     *      canThrough1: true,
//     *      hits1: 1,
//     *      canThrough2: false
//     * }
//     */
//    public function throttleAction()
//    {
//        $canThrough1 = \App\Support\Throttle::tooManyAttempts('test', 1, 1);
//        $hits1 = \App\Support\Throttle::hit('test', 1);
//        $canThrough2 = \App\Support\Throttle::tooManyAttempts('test', 1, 1);
//        \App\Support\Throttle::clear('test');
//        $this->handleResponse([
//            'canThrough1' => !$canThrough1,
//            'hits1' => $hits1,
//            'canThrough2' => !$canThrough2,
//        ]);
//    }
//
//    /**
//     * 正常测试结果：
//     * {
//     *      pass1: true,
//     *      pass2: false
//     * }
//     */
//    public function throttlePassAction()
//    {
//        $pass1 = \App\Support\Throttle::pass('test', 1, 1);
//        $pass2 = \App\Support\Throttle::pass('test', 1, 1);
//        \App\Support\Throttle::clear('test');
//        $this->handleResponse([
//            'pass1' => $pass1,
//            'pass2' => $pass2,
//        ]);
//    }
//
//    /**
//     * 正常测试结果：全部为true
//     */
//    public function strAction()
//    {
//        $this->handleResponse([
//            'res1' => \App\Support\Str::startsWith('foobar', 'foo'),
//            'res2' => \App\Support\Str::startsWith('foobar', 'Foo'),
//            'res3' => !\App\Support\Str::startsWith('foobar', 'Foo', true),
//            'res4' => \App\Support\Str::startsWith('foobar', 'foo', true),
//            'res5' => \App\Support\Str::startsWith('foobar', ''),
//            'res6' => !\App\Support\Str::startsWith('foobar', null),
//            'res7' => !\App\Support\Str::startsWith(null, 'foo'),
//        ]);
//    }
//
//    public function esAction()
//    {
//        $this->handleResponse(Elasticsearch::connection()->search(
//            [
//                'index' => 'test*',
//                'type' => '_doc',
//                'size' => 1,
//                'body' => [
//                    'query' => [
//                        'bool' => [
//                            'must' => [
//                                ['term' => ['id' => '1']],
//                            ]
//                        ]
//                    ]
//                ],
//            ]
//        ));
//    }
//
//    public function carbonAction()
//    {
//        $this->handleResponse(['now' => \Carbon\Carbon::now()->toDateTimeString()]);
//    }
//
//    /**
//     * 正常测试结果：{"content": "test"}
//     *
//     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
//     */
//    public function filesAction()
//    {
//        $fileName = 'test.txt';
//        Storage::disk()->put($fileName, 'test');
//        $this->handleResponse([
//            'content' => Storage::disk()->get($fileName)
//        ]);
//    }
//
//    /**
//     * 正常测试结果：客户端先删除cookie(test)，再调用api后查看cookie(test=test666)
//     */
//    public function cookieAction()
//    {
//        $response = new \App\Services\Http\Response();
//        $response->setCookie('test', 'test666');
//        $this->handleResponse($response);
//    }
//
//    /**
//     * 正常测试结果：test_view
//     */
//    public function viewAction()
//    {
//        $this->handleResponse(View::render('test', ['name' => 'test_view']));
//    }
//
//    /**
//     * 正常测试结果：全为true
//     */
//    public function configAction()
//    {
//        $this->handleResponse(
//            [
//                'test1' => config('logging.default_channel') === 'default',
//                'test2' => config('logging')['default_channel'] === 'default',
//                'test3' => Config::get('logging.default_channel') === 'default',
//                'test4' => Config::get('logging')['default_channel'] === 'default',
//            ]
//        );
//    }
//}
