<?php

namespace App\Support\Transformers;

class ActionTransformer extends BaseTransformer
{
    public function __construct($specTransform = null)
    {
        parent::__construct($specTransform);

        if (!is_null($specTransform)) {
            if (strpos($specTransform, '::') !== false) {
                $specTransform = explode('::', $specTransform);
                $specTransform = array_pop($specTransform);
            }
            $this->specTransform = substr($specTransform, 0, -1 * strlen('Action'));
        }
    }
}
