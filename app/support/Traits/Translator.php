<?php

namespace App\Support\Traits;

trait Translator
{
    /**
     * Translator
     *
     * @param $category
     * @param $message
     * @param array $replace
     * @param null $locale
     * @return array|string|null
     */
    protected static function __($category, $message, $replace = [], $locale = null)
    {
        return \App\Support\Translator::__($category, $message, $replace, $locale);
    }

    /**
     * Errors translator
     *
     * @param $message
     * @param array $replace
     * @param null $locale
     * @return array|string|null
     */
    protected static function __error($message, $replace = [], $locale = null)
    {
        return \App\Support\Translator::__('validation.custom.errors', $message, $replace, $locale);
    }
}
