<?php
/**
 * 增加redis配置
 * @category   laravel-zipkin
 * @author     wareon  <wareon@qq.com>
 * @license    project
 * @link       http://www.speedtrade.net
 * @ctime:     2021/7/21 11:24
 */
use Illuminate\Support\Str;

return [
    'options' => [
        'prefix' => env(
            'ZIPKIN_REDIS_PREFIX',
            Str::slug(env('APP_NAME', 'laravel'), '_').'_zipkin:'
        )
    ],

    'url' => env('ZIPKIN_REDIS_URL'),
    'host' => env('ZIPKIN_REDIS_HOST', '127.0.0.1'),
    'password' => env('ZIPKIN_REDIS_PASSWORD', null),
    'port' => env('ZIPKIN_REDIS_PORT', '6379'),
    'database' => env('ZIPKIN_REDIS_DB', '0')

];
