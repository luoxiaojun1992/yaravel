<?php

namespace App\Support;

class Translator
{
    public static function __($category, $message, $replace = [], $locale = null)
    {
        $formattedMessage = str_replace('.', '@', $message);
        $key = $category . '.' . $formattedMessage;
        /** @var \Illuminate\Translation\Translator $translator */
        $translator = \Registry::get('translator');
        if ($translator->hasForLocale($key, $locale)) {
            return $translator->trans($key, $replace, $locale);
        } else {
            return $message;
        }
    }
}
