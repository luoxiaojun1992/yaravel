<?php

namespace App\Services\Zipkin;

use App\Services\Providers\AbstractProvider;
use App\Support\Traits\PhpSapi;

class ZipkinServiceProvider extends AbstractProvider
{
    use PhpSapi;

    //不能去掉
    protected $defer = true;

    public function register()
    {
        $zipkinConfig = \Config::get('zipkin');
        //文档上写request是在plugin执行阶段才有，实际测试在bootstrap阶段就已经存在
        //因为defer=true，register时至少是plugin执行阶段，request肯定已经有了
        $request = ($this->isCli()) ? null : \Request::getInstance();
        $zipkinTracer = new Tracer($zipkinConfig, $request);
        $this->registry->alias('services.zipkin', $zipkinTracer);
    }

    public function provides()
    {
        return ['services.zipkin'];
    }
}
