<?php

namespace App\Domains\Common\Repositories\CronJobLogs;

use App\Domains\Base\Repositories\Models\OldBaseMysqlModel;

class CronJobLogs extends OldBaseMysqlModel
{
    public $timestamps = false;

    public $incrementing = false;

    const CREATED_AT = null;

    protected $primaryKey = 'job_run_uuid';

    protected $table = 'jing_cron_job_run_time_logs';

    protected $dates = [];

    protected $fillable = [
        'job_id','job_name','job_run_uuid','job_start_time','job_end_time','job_took_time','service_name','errors'
    ];

    protected $rules = [
        'job_id' => 'required|int|',
        'job_name' => 'required|string|',
        'job_run_uuid' => 'required|string|',
        'job_took_time' => 'required|integer',
    ];
}

