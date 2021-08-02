<?php

namespace Wareon\Zipkin\Services\Server;

use Wareon\Zipkin\Thrift\Demo\DemoIf;

/**
 * Demo
 * @package Wareon\Zipkin\Services\Server
 */
class DemoService implements DemoIf
{

    /**
     * 微服务状态
     */
    public function getStatus()
    {
        return [
            'status' => 'success',
            'status_code' => 200,
            'error' => 0,
            'code' => 0,
            'message' => 'ok'
        ];
    }

}
