<?php

namespace App\Services\Exceptions;

use App\Services\Providers\AbstractProvider;
use App\Support\Traits\PhpSapi;

class ExceptionHandlerProvider extends AbstractProvider
{
    use PhpSapi;

    const SERVICE_ID = 'services.exception_handler';

    public function register()
    {
        if (!$this->registry->has(static::SERVICE_ID)) {
            if ($this->isCli()) {
                $exceptionHandler = new ExceptionHandler();
            } else {
                $exceptionHandler = new ExceptionHandler(
                    $this->dispatcher->getRequest()
                );
            }

            $this->registry->alias(static::SERVICE_ID, $exceptionHandler);
        }
    }
}
