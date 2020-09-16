<?php

namespace App\Domains\Common\Support\YiiCache;

/**
 * Class YiiTokenCache
 *
 * {@inheritdoc}
 * 兼容yii1的token cache，新token cache不要使用
 *
 * @package App\Utils\YiiCache
 */
class YiiTokenCache extends YiiCache
{
    protected static $connection = 'yii1_token_cache';
}
