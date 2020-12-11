<?php

namespace App\Domains\Base\Repositories\Models;

use App\Domains\Base\Repositories\Models\Traits\ModelEvents;
use App\Support\Traits\PhpSapi;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AbstractModel
 *
 * @package App\Models
 */
abstract class AbstractModel extends Model
{
    use PhpSapi;
    use ModelEvents;
}
