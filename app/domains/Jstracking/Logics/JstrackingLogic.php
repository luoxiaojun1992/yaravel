<?php

namespace App\Domains\Jstracking\Logics;

use App\Domains\Base\Logics\BaseLogic;
use App\Domains\Jstracking\Repositories\Jstracking\Repository;

class JstrackingLogic extends BaseLogic
{
    protected $singleRepositoryClass = Repository::class;
}
