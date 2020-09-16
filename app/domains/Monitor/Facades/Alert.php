<?php

namespace App\Domains\Monitor\Facades;

use App\Domains\Monitor\Entities\BizAlert;
use App\Domains\Monitor\Services\AlertService;

class Alert
{
    /**
     * 业务报警
     *
     * @param $mid
     * @param $channel
     * @param $errCode
     * @param $errMsg
     * @param $isAgg
     */
    public static function bizAlert($mid, $channel, $errCode, $errMsg, $isAgg = true)
    {
        /** @var AlertService $alertService */
        $alertService = di(AlertService::class);

        $bizAlert = new BizAlert();
        $bizAlert->errorMid = $mid;
        $bizAlert->channel = $channel;
        $bizAlert->errorCode = $errCode;
        $bizAlert->errorMessage = $errMsg;
        $bizAlert->isAgg = $isAgg;
        $bizAlert->filePath = $alertService->getFilePath();

        $alertService->bizAlert($bizAlert);
    }
}
