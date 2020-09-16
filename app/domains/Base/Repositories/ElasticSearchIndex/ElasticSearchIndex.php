<?php

namespace App\Domains\Base\Repositories\ElasticSearchIndex;

use App\Models\OldBaseMysqlModel;

class ElasticSearchIndex extends OldBaseMysqlModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'elastic_search_index';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'created_time';
}
