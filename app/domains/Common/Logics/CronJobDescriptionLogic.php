<?php

namespace App\Domains\Common\Logics;

use App\Domains\Base\Logics\BaseLogic;
use App\Domains\Common\Repositories\CronJobDescription\Repository;
use App\Domains\Common\Repositories\CronJobLogs\Repository as logRepository;
use App\Domains\Common\Repositories\CronJobDescription\CronJobDescription;
use PascalDeVink\ShortUuid\ShortUuid;

class CronJobDescriptionLogic extends BaseLogic
{
    /**
     * @param $commandName
     * @param $args
     * @param $start
     * @param $errors
     * @return array
     * @throws \Exception
     */
    public function saveJobLog($commandName, $args, $start, $errors)
    {
        $serviceName = config('console.command_service_name');
        $name = "php artisan ".implode(" ", $args);
        $end = date("Y-m-d H:i:s");
        $cronJob = CronJobDescription::query()->where(['job_name' =>$name])->first();
        if(isset($cronJob->id)){
            $cronJobId =  $cronJob->id;
        } else {
            $repository = di(Repository::class);
            $data['job_name'] = $name;
            $data['job_class_name'] = $commandName;
            $data['create_time'] =  date("Y-m-d H:i:s");
            $data['service_name'] =  $serviceName;
            $cronJob =  $repository->addOne($data);
            $cronJobId = isset($cronJob['id']) ? $cronJob['id'] : 0;
        }
        //创建日志
        $repository = di(logRepository::class);
        $data['job_id'] = $cronJobId;
        $data['job_run_uuid'] = ShortUuid::uuid1();
        $data['job_name'] =  $name;
        $data['job_start_time'] = $start;
        $data['job_end_time'] = $end;
        $data['job_took_time'] = strtotime($end)-strtotime($start);
        $data['service_name'] = $serviceName;
        $data['errors'] = ($errors == 0) ? "" : $errors;
        return $repository->addOne($data);
    }
}
