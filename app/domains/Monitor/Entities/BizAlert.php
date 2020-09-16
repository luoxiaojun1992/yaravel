<?php

namespace App\Domains\Monitor\Entities;

class BizAlert
{
    public $errorMid;

    public $channel;

    public $module;

    public $errorCode;

    public $errorMessage;

    public $filePath;

    public $objectId;

    public $subObjectId;

    public $params;

    public $isAgg = true;
}
