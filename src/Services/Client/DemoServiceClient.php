<?php

namespace Wareon\Zipkin\Services\Client;

/**
 * Class DemoServiceClient
 * @method static array getStatus()
 */

class DemoServiceClient extends BaseClient
{
    public function __construct()
    {
        $this->host = config('zipkin.rpc_host', '127.0.0.1');
        $this->port = config('zipkin.rpc_port', '8911');
        $this->rpcClient = config('zipkin.client_class');
    }
}
