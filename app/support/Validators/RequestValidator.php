<?php

namespace App\Support\Validators;

use App\Services\Http\Request;

class RequestValidator extends Validator
{
    public static function createFromRequest(Request $request)
    {
        return new static($request->all());
    }
}
