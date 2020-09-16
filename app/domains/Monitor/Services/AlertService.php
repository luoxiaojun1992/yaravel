<?php

namespace App\Domains\Monitor\Services;

use App\Domains\Base\Services\BaseService;
use App\Domains\Common\Support\Auth;
use App\Domains\Monitor\Entities\BizAlert;

class AlertService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
        $this->internalBaseUrl = config('api.v2_monitor_domain');
    }

    /**
     * 业务报警
     *
     * @param $alert
     */
    public function bizAlert(BizAlert $alert)
    {
        $params = [
            'errorMid' => $alert->errorMid,
            'channel' => $alert->channel,
            'module' => $alert->module ?: config('alert.module'),
            'errorCode' => $alert->errorCode,
            'errorMessage' => $alert->errorMessage,
            'filePath' => $alert->filePath ?: $this->getFilePath(),
        ];
        if (!is_null($alert->objectId)) {
            $params['objectId'] = $alert->objectId;
        }
        if (!is_null($alert->subObjectId)) {
            $params['subObjectId'] = $alert->subObjectId;
        }
        if (!is_null($alert->params)) {
            $params['params'] = $alert->params;
        }
        if (!is_null($alert->isAgg)) {
            $params['isAgg'] = intval($alert->isAgg);
        }

        try {
            $this->callInternalApiWithoutAuth(
                '/api/internal/monitor/biz-alert',
                'POST',
                $params,
                [],
                null,
                Auth::authorization(),
                null,
                5
            );
        } catch (\Throwable $e) {
            //call internal内部已经记录了日志
        }
    }

    /**
     * 发送报警通知
     *
     * @param $subject
     * @param $content
     * @param array|null $groups
     * @param array|null $channels
     * @param int $id
     * @param string $alertLevel
     */
    public function notify($subject, $content, $groups = null, $channels = null, $id = 0, $alertLevel = 'info')
    {
        try {
            $this->callInternalApiWithoutAuth(
                '/api/internal/monitor/notify',
                'POST',
                [
                    'id' => $id,
                    'alert_level' => $alertLevel,
                    'subject' => $subject,
                    'content' => $content,
                    'alert_groups' => $groups,
                    'alert_channels' => $channels,
                ],
                [],
                null,
                null,
                null,
                5
            );
        } catch (\Throwable $e) {
            //call internal内部已经记录了日志
        }
    }

    /**
     * 获取调用方的文件位置
     *
     * @param int $index
     * @param int $backTraceLimit
     * @return string
     */
    public function getFilePath($index = 1, $backTraceLimit = 2)
    {
        try {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, $backTraceLimit);
            $file = isset($backtrace[$index]['file']) ? $backtrace[$index]['file'] : '';
            $line = isset($backtrace[$index]['line']) ? $backtrace[$index]['line'] : '';
            return $file . ':' . $line;
        } catch (\Throwable $e) {
            \Log::error('Biz Alert Get File Path Error:' . $e->getMessage() . '|' . $e->getTraceAsString());
        }

        return ':';
    }
}
