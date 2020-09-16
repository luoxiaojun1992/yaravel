<?php

namespace App\Domains\Common\Repositories\CronJobDescription;

use App\Domains\Base\Repositories\Models\OldBaseMysqlModel;

class CronJobDescription extends OldBaseMysqlModel
{
    public $timestamps = false;

    protected $table = 'jing_cron_job_description';

    protected $fillable = [
        'id','job_class_name', 'job_name', 'job_description','owner','is_custom','custom_for_client','create_time','service_name'
    ];
}
