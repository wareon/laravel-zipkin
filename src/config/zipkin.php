<?php
/**
 * 主配置文件
 * @category   laravel-zipkin
 * @author     wareon  <wareon@qq.com>
 * @license    project
 * @link       http://www.speedtrade.net
 * @ctime:     2021/7/21 11:24
 */

return [
    'enable' => env('ZIPKIN_ENABLE', false),
    'parent_prefix' => env('ZIPKIN_REDIS_PARENT_PREFIX', null),
    'endpoint_url' => env('ZIPKIN_ENDPOINT_URL', 'http://localhost:9411/api/v2/spans'),

    'rpc_name' => env('MICRO_SERVICE_NAME', 'Laravel zipkin RPC'),
    'rpc_host' => env('MICRO_SERVICE_HOST', '127.0.0.1'),
    'rpc_port' => env('MICRO_SERVICE_PORT', '8911'),
    'rpc_timeout' => env('MICRO_SERVICE_TIMEOUT', 10000),

    'processor_class' => \Wareon\Zipkin\Thrift\Demo\DemoProcessor::class,
    'service_class' => \Wareon\Zipkin\Services\Server\DemoService::class,
    'client_class' => \Wareon\Zipkin\Services\Client\DemoServiceClient::class,
];
