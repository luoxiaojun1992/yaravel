<?php

use Yaf\Plugin_Abstract as YafPlugin;
use Yaf\Request_Abstract as YafRequest;
use Yaf\Response_Abstract as YafResponse;

/**
 * Class QueryLogTracerPlugin
 *
 * @author overtrue <i@overtrue.me>
 */
class QueryLogTracerPlugin extends YafPlugin
{
    protected $enableQueryLog;

    protected function enableQueryLog()
    {
        if (!is_null($this->enableQueryLog)) {
            return $this->enableQueryLog;
        }

        return ($this->enableQueryLog = (Config::get('app')['debug'] && Config::get('debug')['query_log']));
    }

    /**
     * @return \App\Services\Elasticsearch\Tracer
     */
    protected function getElasticsearchTracer()
    {
        /** @var \App\Services\Elasticsearch\Tracer $elasticsearchTracer */
        $elasticsearchTracer = Registry::get('elasticsearch.tracer');
        return $elasticsearchTracer;
    }

    /**
     * @param \Yaf\Request_Abstract  $request
     * @param \Yaf\Response_Abstract $response
     */
    public function routerStartup(YafRequest $request, YafResponse $response)
    {
        /* 在路由之前执行,这个钩子里，你可以做url重写等功能 */
        if ($this->enableQueryLog()) {
            DB::enableQueryLog();
            $this->getElasticsearchTracer()->enable();
        }
    }

    /**
     * @param \Yaf\Request_Abstract  $request
     * @param \Yaf\Response_Abstract $response
     */
    public function routerShutdown(YafRequest $request, YafResponse $response)
    {
        /* 路由完成后，在这个钩子里，你可以做登陆检测等功能*/
    }

    /**
     * @param \Yaf\Request_Abstract  $request
     * @param \Yaf\Response_Abstract $response
     */
    public function dispatchLoopStartup(YafRequest $request, YafResponse $response)
    {
        //
    }

    /**
     * @param \Yaf\Request_Abstract  $request
     * @param \Yaf\Response_Abstract $response
     */
    public function preDispatch(YafRequest $request, YafResponse $response)
    {
        //
    }

    /**
     * @param \Yaf\Request_Abstract  $request
     * @param \Yaf\Response_Abstract $response
     */
    public function postDispatch(YafRequest $request, YafResponse $response)
    {
        //
    }

    /**
     * @param \Yaf\Request_Abstract  $request
     * @param \Yaf\Response_Abstract $response
     */
    public function dispatchLoopShutdown(YafRequest $request, YafResponse $response)
    {
        /* final hook
           in this hook user can do login or implement layout */
        if ($this->enableQueryLog()) {
            $body = json_decode($response->getBody(), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $debugField = Config::get('debug.field', 'debug');
                $body[$debugField]['query']['sql'] = DB::getQueryLog();
                $body[$debugField]['query']['es'] = $this->getElasticsearchTracer()->getLoggers();
                $response->setBody(json_encode($body));
            }
        }
    }
}
