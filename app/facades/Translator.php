<?php

/**
 * Class Translator
 *
 * @method static bool hasForLocale(string $key, string|null $locale = null)
 * @method static bool has(string $key, string|null $locale = null, bool $fallback = true)
 * @method static string|array|null trans(string $key, array $replace = [], string $locale = null)
 * @method static string|array|null get(string $key, array $replace = [], string|null $locale = null, bool $fallback = true)
 * @method static string|array|null getFromJson(string $key, array $replace = [], string $locale = null)
 * @method static string transChoice(string $key, int|array|\Countable $number, array $replace = [], string $locale = null)
 * @method static string choice(string $key, int|array|\Countable $number, array $replace = [], string $locale = null)
 */
class Translator extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'translator';
    }
}
