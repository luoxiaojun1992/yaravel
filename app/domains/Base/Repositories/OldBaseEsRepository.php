<?php

namespace App\Domains\Base\Repositories;

class OldBaseEsRepository extends BaseEsRepository
{
    //Elasticsearch Connection Name
    protected $connection = 'yii1';

    protected $type = 'wechat_customer';
}
