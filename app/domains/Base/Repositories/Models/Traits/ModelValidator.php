<?php

namespace App\Domains\Base\Repositories\Models\Traits;

use Illuminate\Validation\ValidationException;

trait ModelValidator
{
    /**
     * @var array Validation rules
     */
    protected $rules = [
        //
    ];

    /**
     * @var array Validation error messages
     */
    protected $ruleMessages = [
        //
    ];

    /**
     * @var array Validation errors
     */
    protected $errors = [
        //
    ];

    /**
     * @var bool
     */
    protected $needValidate = true;

    /**
     * @var bool
     */
    protected $returnErrors = true;

    /**
     * Model data validation, validate before save
     *
     * @throws \Exception
     */
    protected function validate()
    {
        if ($this->fireModelEvent('validating') === false) {
            $this->setErrors([static::class . ' validating error']);
            if ($this->isReturnErrors()) {
                return false;
            } else {
                throw new \Exception(static::class . ' validating error');
            }
        }

        if ($this->isNeedValidate()) {
            $validator = \Validator::make($this->getAttributes(), $this->rules, $this->ruleMessages);
            if ($this->isReturnErrors()) {
                if ($validator->fails()) {
                    $this->setErrors($validator->errors()->toArray());
                    return false;
                }
            } else {
                try {
                    $validator->validate();
                } catch (ValidationException $e) {
                    $this->setErrors($e->validator->errors()->toArray());
                    throw $e;
                }
            }
        }

        $this->fireModelEvent('validated', false);

        return true;
    }

    /**
     * Get validation errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Set validation errors
     *
     * @param array $errors
     * @return $this
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
        return $this;
    }

    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    /**
     * @return bool
     */
    public function isNeedValidate()
    {
        return $this->needValidate;
    }

    /**
     * @param bool $needValidate
     * @return $this
     */
    public function setNeedValidate($needValidate)
    {
        $this->needValidate = $needValidate;
        return $this;
    }

    /**
     * @return bool
     */
    public function isReturnErrors()
    {
        return $this->returnErrors;
    }

    /**
     * @param bool $returnErrors
     * @return $this
     */
    public function setReturnErrors($returnErrors)
    {
        $this->returnErrors = $returnErrors;
        return $this;
    }
}
