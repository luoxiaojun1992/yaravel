<?php

namespace App\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\Process\Process;

class TestFrameworkCommand extends Command
{
    protected $name = 'test:framework';
    protected $description = '框架基本功能测试，调用TestController';

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $this->startTestServer(function () {
            $this->testSuites();
            $this->info('Success.');
        });
    }

    protected function testSuites()
    {
        $this->evaluateDuration(function () {
            $this->testJson();
        });
        $this->evaluateDuration(function () {
            $this->testDb();
        });
        $this->evaluateDuration(function () {
            $this->testPagination();
        });
        $this->evaluateDuration(function () {
            $this->testApi();
        });
        $this->evaluateDuration(function () {
            $this->testApiFail();
        });
        $this->evaluateDuration(function () {
            $this->testDi();
        });
        $this->evaluateDuration(function () {
            $this->testLaravelDi();
        });
        $this->evaluateDuration(function () {
            $this->testLaravelDiMake();
        });
        $this->evaluateDuration(function () {
            $this->testDiLaravelDiMake();
        });
        $this->evaluateDuration(function () {
            $this->testDiLaravelDiInstance();
        });
        $this->evaluateDuration(function () {
            $this->testCompatibleLaravelDi();
        });
        $this->evaluateDuration(function () {
            $this->testTranslate();
        });
        $this->evaluateDuration(function () {
            $this->testTranslateChinese();
        });
        $this->evaluateDuration(function () {
            $this->testTranslateEnglish();
        });
        $this->evaluateDuration(function () {
            $this->testQueue();
        });
        $this->evaluateDuration(function () {
            $this->testRedis();
        });
        $this->evaluateDuration(function () {
            $this->testLog();
        });
        $this->evaluateDuration(function () {
            $this->testValidate();
        });
        $this->evaluateDuration(function () {
            $this->testSession();
        });
        $this->evaluateDuration(function () {
            $this->testException();
        });
        $this->evaluateDuration(function () {
            $this->testArr();
        });
        $this->evaluateDuration(function () {
            $this->testRequest();
        });
        $this->evaluateDuration(function () {
            $this->testRequestHeader();
        });
        $this->evaluateDuration(function () {
            $this->testMail();
        });
        $this->evaluateDuration(function () {
            $this->testLock();
        });
        $this->evaluateDuration(function () {
            $this->testLockTTL();
        });
        $this->evaluateDuration(function () {
            $this->testCache();
        });
        $this->evaluateDuration(function () {
            $this->testThrottle();
        });
        $this->evaluateDuration(function () {
            $this->testThrottlePass();
        });
        $this->evaluateDuration(function () {
            $this->testStr();
        });
        $this->evaluateDuration(function () {
            $this->testEs();
        });
        $this->evaluateDuration(function () {
            $this->testCarbon();
        });
        $this->evaluateDuration(function () {
            $this->testFiles();
        });
        $this->evaluateDuration(function () {
            $this->testCookie();
        });
        $this->evaluateDuration(function () {
            $this->testView();
        });
        $this->evaluateDuration(function () {
            $this->testConfig();
        });
    }

    /**
     * @param $callback
     * @throws \Exception
     */
    protected function startTestServer($callback)
    {
        $process = new Process('cd ' . BASE_PATH . '/public && php -S 0.0.0.0:8888');
        $process->start();

        $startTime = time();
        while (!$process->isRunning()) {
            if (time() - $startTime > 5) {
                throw new \Exception('Test server cannot be started.');
            }
        }

        sleep(1);

        call_user_func($callback);

        $process->stop();

        shell_exec('ps aux | grep \'php -S 0.0.0.0:8888\' | grep -v grep | awk \'{print $2}\' | xargs kill -9');
    }

    /**
     * @throws \Exception
     */
    protected function testJson()
    {
        $this->info('Testing json');

        $res = $this->doHttpRequest(
            'GET',
            '/test/json'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: json');
        }

        if ($res->getBody()->getContents() !== json_encode(['foo' => 'bar'])) {
            throw new \Exception('Failed test: json');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testDb()
    {
        $this->info('Testing db');

        $res = $this->doHttpRequest(
            'GET',
            '/test/db'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: db');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: db');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: db');
        }

        if (!isset($arr['id'])) {
            throw new \Exception('Failed test: db');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testPagination()
    {
        $this->info('Testing pagination');

        $res = $this->doHttpRequest(
            'GET',
            '/test/pagination'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: pagination');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: pagination');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: pagination');
        }

        if (!isset($arr['items'])) {
            throw new \Exception('Failed test: pagination');
        }

        if (count($arr['items']) <= 0) {
            throw new \Exception('Failed test: pagination');
        }

        if (!isset($arr['_meta']['totalCount'])) {
            throw new \Exception('Failed test: pagination');
        }

        if ($arr['_meta']['totalCount'] <= 0) {
            throw new \Exception('Failed test: pagination');
        }

        if (!isset($arr['_meta']['pageCount'])) {
            throw new \Exception('Failed test: pagination');
        }

        if ($arr['_meta']['pageCount'] <= 0) {
            throw new \Exception('Failed test: pagination');
        }

        if (!isset($arr['_meta']['currentPage'])) {
            throw new \Exception('Failed test: pagination');
        }

        if ($arr['_meta']['currentPage'] <= 0) {
            throw new \Exception('Failed test: pagination');
        }

        if (!isset($arr['_meta']['perPage'])) {
            throw new \Exception('Failed test: pagination');
        }

        if ($arr['_meta']['perPage'] <= 0) {
            throw new \Exception('Failed test: pagination');
        }

        if (!isset($arr['_links'])) {
            throw new \Exception('Failed test: pagination');
        }

        if (!array_key_exists('previous', $arr['_links'])) {
            throw new \Exception('Failed test: pagination');
        }

        if (!array_key_exists('next', $arr['_links'])) {
            throw new \Exception('Failed test: pagination');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testApi()
    {
        $this->info('Testing api');

        $res = $this->doHttpRequest(
            'GET',
            '/test/api'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: api');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: api');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: api');
        }

        if (!isset($arr['code'])) {
            throw new \Exception('Failed test: api');
        }

        if ($arr['code'] !== 0) {
            throw new \Exception('Failed test: api');
        }

        if (!isset($arr['msg'])) {
            throw new \Exception('Failed test: api');
        }

        if (!isset($arr['message'])) {
            throw new \Exception('Failed test: api');
        }

        if (!isset($arr['data']['items'])) {
            throw new \Exception('Failed test: api');
        }

        if (count($arr['data']['items']) <= 0) {
            throw new \Exception('Failed test: api');
        }

        if (!isset($arr['data']['_meta']['totalCount'])) {
            throw new \Exception('Failed test: api');
        }

        if ($arr['data']['_meta']['totalCount'] <= 0) {
            throw new \Exception('Failed test: api');
        }

        if (!isset($arr['data']['_meta']['pageCount'])) {
            throw new \Exception('Failed test: api');
        }

        if ($arr['data']['_meta']['pageCount'] <= 0) {
            throw new \Exception('Failed test: api');
        }

        if (!isset($arr['data']['_meta']['currentPage'])) {
            throw new \Exception('Failed test: api');
        }

        if ($arr['data']['_meta']['currentPage'] <= 0) {
            throw new \Exception('Failed test: api');
        }

        if (!isset($arr['data']['_meta']['perPage'])) {
            throw new \Exception('Failed test: api');
        }

        if ($arr['data']['_meta']['perPage'] <= 0) {
            throw new \Exception('Failed test: api');
        }

        if (!isset($arr['data']['_links'])) {
            throw new \Exception('Failed test: api');
        }

        if (!array_key_exists('previous', $arr['data']['_links'])) {
            throw new \Exception('Failed test: api');
        }

        if (!array_key_exists('next', $arr['data']['_links'])) {
            throw new \Exception('Failed test: api');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testApiFail()
    {
        $this->info('Testing api fail');

        $res = $this->doHttpRequest(
            'GET',
            '/test/apifail'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: api fail');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: api fail');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: api fail');
        }

        if (!isset($arr['code'])) {
            throw new \Exception('Failed test: api fail');
        }

        if ($arr['code'] === 0) {
            throw new \Exception('Failed test: api fail');
        }

        if (!isset($arr['msg'])) {
            throw new \Exception('Failed test: api fail');
        }

        if (!isset($arr['message'])) {
            throw new \Exception('Failed test: api fail');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testDi()
    {
        $this->info('Testing di');

        $res = $this->doHttpRequest(
            'GET',
            '/test/di'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: di');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: di');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: di');
        }

        if (!isset($arr['action'])) {
            throw new \Exception('Failed test: di');
        }

        if ($arr['action'] !== 'di') {
            throw new \Exception('Failed test: di');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testLaravelDi()
    {
        $this->info('Testing laravel di');

        $res = $this->doHttpRequest(
            'GET',
            '/test/laraveldi'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: laravel di');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: laravel di');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: laravel di');
        }

        if (!isset($arr['action'])) {
            throw new \Exception('Failed test: laravel di');
        }

        if ($arr['action'] !== 'laravelDi') {
            throw new \Exception('Failed test: laravel di');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testLaravelDiMake()
    {
        $this->info('Testing laravel di make');

        $res = $this->doHttpRequest(
            'GET',
            '/test/laraveldimake'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: laravel di make');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: laravel di make');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: laravel di make');
        }

        if (!isset($arr['transformer1'])) {
            throw new \Exception('Failed test: laravel di make');
        }

        if (!isset($arr['transformer2'])) {
            throw new \Exception('Failed test: laravel di make');
        }

        if ($arr['transformer1'] === $arr['transformer2']) {
            throw new \Exception('Failed test: laravel di make');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testDiLaravelDiMake()
    {
        $this->info('Testing di laravel di make');

        $res = $this->doHttpRequest(
            'GET',
            '/test/dilaraveldimake'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: di laravel di make');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: di laravel di make');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: di laravel di make');
        }

        if (!isset($arr['transformer1'])) {
            throw new \Exception('Failed test: di laravel di make');
        }

        if (!isset($arr['transformer2'])) {
            throw new \Exception('Failed test: di laravel di make');
        }

        if ($arr['transformer1'] === $arr['transformer2']) {
            throw new \Exception('Failed test: di laravel di make');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testDiLaravelDiInstance()
    {
        $this->info('Testing di laravel di instance');

        $res = $this->doHttpRequest(
            'GET',
            '/test/dilaraveldiinstance'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: di laravel di instance');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: di laravel di instance');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: di laravel di instance');
        }

        if (!isset($arr['transformer1'])) {
            throw new \Exception('Failed test: di laravel di instance');
        }

        if (!isset($arr['transformer2'])) {
            throw new \Exception('Failed test: di laravel di instance');
        }

        if ($arr['transformer1'] !== $arr['transformer2']) {
            throw new \Exception('Failed test: di laravel di instance');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testCompatibleLaravelDi()
    {
        $this->info('Testing compatible laravel di');

        $res = $this->doHttpRequest(
            'GET',
            '/test/compatibleLaravelDi'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: compatible laravel di');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: compatible laravel di');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: compatible laravel di');
        }

        if (!isset($arr['action'])) {
            throw new \Exception('Failed test: compatible laravel di');
        }

        if ($arr['action'] !== 'compatibleLaravelDi') {
            throw new \Exception('Failed test: compatible laravel di');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testTranslate()
    {
        $this->info('Testing translate');

        $res = $this->doHttpRequest(
            'GET',
            '/test/translate'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: translate');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: translate');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: translate');
        }

        if (!isset($arr['translation'])) {
            throw new \Exception('Failed test: translate');
        }

        if ($arr['translation'] !== '用户名或密码错误。') {
            throw new \Exception('Failed test: translate');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testTranslateChinese()
    {
        $this->info('Testing translate chinese');

        $res = $this->doHttpRequest(
            'GET',
            '/test/translate',
            [
                'J-CustomerLanguage' => 'zh-CN',
            ]
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: translate chinese');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: translate chinese');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: translate chinese');
        }

        if (!isset($arr['translation'])) {
            throw new \Exception('Failed test: translate chinese');
        }

        if ($arr['translation'] !== '用户名或密码错误。') {
            throw new \Exception('Failed test: translate chinese');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testTranslateEnglish()
    {
        $this->info('Testing translate English');

        $res = $this->doHttpRequest(
            'GET',
            '/test/translate',
            [
                'J-CustomerLanguage' => 'en',
            ]
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: translate English');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: translate English');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: translate English');
        }

        if (!isset($arr['translation'])) {
            throw new \Exception('Failed test: translate English');
        }

        if ($arr['translation'] !== 'These credentials do not match our records.') {
            throw new \Exception('Failed test: translate English');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testQueue()
    {
        $this->info('Testing queue');

        $res = $this->doHttpRequest(
            'GET',
            '/test/queue'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: queue');
        }

        $body = $res->getBody()->getContents();
        if ($body) {
            throw new \Exception('Failed test: queue');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testRedis()
    {
        $this->info('Testing redis');

        $res = $this->doHttpRequest(
            'GET',
            '/test/redis'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: redis');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: redis');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: redis');
        }

        if (!isset($arr['foo'])) {
            throw new \Exception('Failed test: redis');
        }

        if ($arr['foo'] !== 'bar') {
            throw new \Exception('Failed test: redis');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testLog()
    {
        $this->info('Testing log');

        $res = $this->doHttpRequest(
            'GET',
            '/test/log'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: log');
        }

        $body = $res->getBody()->getContents();
        if ($body) {
            throw new \Exception('Failed test: log');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testValidate()
    {
        $this->info('Testing validate');

        $res = $this->doHttpRequest(
            'GET',
            '/test/validate'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: validate');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: validate');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: validate');
        }

        if (!isset($arr['res'])) {
            throw new \Exception('Failed test: validate');
        }

        if ($arr['res'] !== true) {
            throw new \Exception('Failed test: validate');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testSession()
    {
        $this->info('Testing session');

        $res = $this->doHttpRequest(
            'GET',
            '/test/session',
            [],
            CookieJar::fromArray([], '127.0.0.1:8888')
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: session');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: session');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: session');
        }

        if (!isset($arr['value'])) {
            throw new \Exception('Failed test: session');
        }

        if ($arr['value'] !== 'bar') {
            throw new \Exception('Failed test: session');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testException()
    {
        $this->info('Testing exception');

        try {
            $this->doHttpRequest(
                'GET',
                '/test/exception'
            );
        } catch (ServerException $e) {
            $res = $e->getResponse();

            if ($res->getStatusCode() === 200) {
                throw new \Exception('Failed test: exception');
            }

            $body = $res->getBody()->getContents();
            if (!$body) {
                throw new \Exception('Failed test: exception');
            }

            $arr = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Failed test: exception');
            }

            if (!isset($arr['code'])) {
                throw new \Exception('Failed test: exception');
            }

            if ($arr['code'] === 0) {
                throw new \Exception('Failed test: exception');
            }

            if (!isset($arr['msg'])) {
                throw new \Exception('Failed test: exception');
            }

            if (!isset($arr['message'])) {
                throw new \Exception('Failed test: exception');
            }

            return;
        }

        throw new \Exception('Failed test: exception');
    }

    /**
     * @throws \Exception
     */
    protected function testArr()
    {
        $this->info('Testing arr');

        $res = $this->doHttpRequest(
            'GET',
            '/test/arr'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: arr');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: arr');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: arr');
        }

        if (count($arr) <= 0) {
            throw new \Exception('Failed test: arr');
        }

        foreach ($arr as $result) {
            if ($result !== true) {
                throw new \Exception('Failed test: arr');
            }
        }
    }

    /**
     * @throws \Exception
     */
    protected function testRequest()
    {
        $this->info('Testing request');

        $res = $this->doHttpRequest(
            'GET',
            '/test/request'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: request');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: request');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: request');
        }

        if (!isset($arr['method'])) {
            throw new \Exception('Failed test: request');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testRequestHeader()
    {
        $this->info('Testing request header');

        $res = $this->doHttpRequest(
            'GET',
            '/test/requestheader'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: request header');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: request header');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: request header');
        }

        if (count($arr) <= 0) {
            throw new \Exception('Failed test: request header');
        }

        foreach ($arr as $result) {
            if ($result !== true) {
                throw new \Exception('Failed test: request header');
            }
        }
    }

    /**
     * @throws \Exception
     */
    protected function testMail()
    {
        $this->info('Testing mail');

        $res = $this->doHttpRequest(
            'GET',
            '/test/mail'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: mail');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: mail');
        }

        if ($body !== '1') {
            throw new \Exception('Failed test: mail');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testLock()
    {
        $this->info('Testing lock');

        $res = $this->doHttpRequest(
            'GET',
            '/test/lock'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: lock');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: lock');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: lock');
        }

        if (!isset($arr['test1'])) {
            throw new \Exception('Failed test: lock');
        }

        if ($arr['test1'] !== 'foo') {
            throw new \Exception('Failed test: lock');
        }

        if (!isset($arr['test2'])) {
            throw new \Exception('Failed test: lock');
        }

        if ($arr['test2'] !== false) {
            throw new \Exception('Failed test: lock');
        }

        if (!isset($arr['test3'])) {
            throw new \Exception('Failed test: lock');
        }

        if ($arr['test3'] !== 'foo') {
            throw new \Exception('Failed test: lock');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testLockTTL()
    {
        $this->info('Testing lock ttl');

        $res = $this->doHttpRequest(
            'GET',
            '/test/lockttl'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: lock ttl');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: lock ttl');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: lock ttl');
        }

        if (!isset($arr['ttl'])) {
            throw new \Exception('Failed test: lock ttl');
        }

        if ($arr['ttl'] <= 0) {
            throw new \Exception('Failed test: lock ttl');
        }

        if (!isset($arr['newTTL'])) {
            throw new \Exception('Failed test: lock ttl');
        }

        if ($arr['newTTL'] <= 0) {
            throw new \Exception('Failed test: lock ttl');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testCache()
    {
        $this->info('Testing cache');

        $res = $this->doHttpRequest(
            'GET',
            '/test/cache'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: cache');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: cache');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: cache');
        }

        if (!isset($arr['value1'])) {
            throw new \Exception('Failed test: cache');
        }

        if ($arr['value1'] !== 'bar') {
            throw new \Exception('Failed test: cache');
        }

        if (!array_key_exists('value2', $arr)) {
            throw new \Exception('Failed test: cache');
        }

        if (!is_null($arr['value2'])) {
            throw new \Exception('Failed test: cache');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testThrottle()
    {
        $this->info('Testing throttle');

        $res = $this->doHttpRequest(
            'GET',
            '/test/throttle'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: throttle');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: throttle');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: throttle');
        }

        if (!isset($arr['canThrough1'])) {
            throw new \Exception('Failed test: throttle');
        }

        if ($arr['canThrough1'] !== true) {
            throw new \Exception('Failed test: throttle');
        }

        if (!isset($arr['hits1'])) {
            throw new \Exception('Failed test: throttle');
        }

        if ($arr['hits1'] !== 1) {
            throw new \Exception('Failed test: throttle');
        }

        if (!isset($arr['canThrough2'])) {
            throw new \Exception('Failed test: throttle');
        }

        if ($arr['canThrough2'] !== false) {
            throw new \Exception('Failed test: throttle');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testThrottlePass()
    {
        $this->info('Testing throttle pass');

        $res = $this->doHttpRequest(
            'GET',
            '/test/throttlepass'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: throttle pass');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: throttle pass');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: throttle pass');
        }

        if (!isset($arr['pass1'])) {
            throw new \Exception('Failed test: throttle pass');
        }

        if ($arr['pass1'] !== true) {
            throw new \Exception('Failed test: throttle pass');
        }

        if (!isset($arr['pass2'])) {
            throw new \Exception('Failed test: throttle pass');
        }

        if ($arr['pass2'] !== false) {
            throw new \Exception('Failed test: throttle pass');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testStr()
    {
        $this->info('Testing str');

        $res = $this->doHttpRequest(
            'GET',
            '/test/str'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: str');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: str');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: str');
        }

        if (count($arr) <= 0) {
            throw new \Exception('Failed test: str');
        }

        foreach ($arr as $result) {
            if ($result !== true) {
                throw new \Exception('Failed test: str');
            }
        }
    }

    /**
     * @throws \Exception
     */
    protected function testEs()
    {
        $this->info('Testing es');

        $res = $this->doHttpRequest(
            'GET',
            '/test/es'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: es');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: es');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: es');
        }

        if (!isset($arr['hits'])) {
            throw new \Exception('Failed test: es');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testCarbon()
    {
        $this->info('Testing carbon');

        $res = $this->doHttpRequest(
            'GET',
            '/test/carbon'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: carbon');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: carbon');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: carbon');
        }

        if (!isset($arr['now'])) {
            throw new \Exception('Failed test: carbon');
        }

        if (date('Y-m-d H:i:s', strtotime($arr['now'])) !== $arr['now']) {
            throw new \Exception('Failed test: carbon');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testFiles()
    {
        $this->info('Testing files');

        $res = $this->doHttpRequest(
            'GET',
            '/test/files'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: files');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: files');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: files');
        }

        if (!isset($arr['content'])) {
            throw new \Exception('Failed test: files');
        }

        if ($arr['content'] !== 'test') {
            throw new \Exception('Failed test: files');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testCookie()
    {
        $cookieStorage = CookieJar::fromArray([], '127.0.0.1:8888');

        $this->info('Testing cookie');

        $res = $this->doHttpRequest(
            'GET',
            '/test/cookie',
            [],
            $cookieStorage
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: cookie');
        }

        $body = $res->getBody()->getContents();
        if ($body) {
            throw new \Exception('Failed test: cookie');
        }

        if ($cookieStorage->getCookieByName('test')->getValue() !== 'test666') {
            throw new \Exception('Failed test: cookie');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testView()
    {
        $this->info('Testing view');

        $res = $this->doHttpRequest(
            'GET',
            '/test/view'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: view');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: view');
        }

        if ($body !== 'test_view') {
            throw new \Exception('Failed test: view');
        }
    }

    /**
     * @throws \Exception
     */
    protected function testConfig()
    {
        $this->info('Testing config');

        $res = $this->doHttpRequest(
            'GET',
            '/test/config'
        );

        if ($res->getStatusCode() !== 200) {
            throw new \Exception('Failed test: config');
        }

        $body = $res->getBody()->getContents();
        if (!$body) {
            throw new \Exception('Failed test: config');
        }

        $arr = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed test: config');
        }

        if (count($arr) <= 0) {
            throw new \Exception('Failed test: config');
        }

        foreach ($arr as $result) {
            if ($result !== true) {
                throw new \Exception('Failed test: config');
            }
        }
    }

    protected function evaluateDuration($callback)
    {
        $start = microtime(true);

        $res = call_user_func($callback);

        $this->info('Duration: ' . (string)(microtime(true) - $start) . 's');

        return $res;
    }

    protected function doHttpRequest(
        $method,
        $uri,
        $headers = [],
        $cookieStorage = null
    )
    {
        $httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8888',
            'timeout'  => 5.0,
        ]);

        $options = [
            'headers' => $headers,
        ];

        if (!is_null($cookieStorage)) {
            $options['cookies'] = $cookieStorage;
        }

        return $httpClient->request(
            $method,
            $uri,
            $options
        );
    }
}
