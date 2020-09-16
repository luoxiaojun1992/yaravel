<?php

namespace App\Domains\Base\Repositories\ElasticSearchIndex;

use App\Domains\Base\Repositories\BaseRepository;

class Repository extends BaseRepository
{
    /** @var string $model */
    protected $model = ElasticSearchIndex::class; //model class
    protected $filterable = ['*'];
}
