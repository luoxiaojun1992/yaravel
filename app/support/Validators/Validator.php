<?php

namespace App\Support\Validators;

class Validator
{
    protected $rules = [];
    protected $messages = [];
    protected $data = [];
    protected $errors = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @param bool $throwException
     * @return bool
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate($throwException = true)
    {
        $validator = \Validator::make($this->data, $this->rules, $this->messages);

        if ($throwException) {
            $validator->validate();
            return true;
        } else {
            if ($validator->fails()) {
                $this->errors = $validator->errors()->toArray();
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
