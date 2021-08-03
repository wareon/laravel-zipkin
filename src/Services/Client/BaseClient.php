<?php
/**
 * 微服务客户端
 */

namespace Wareon\Zipkin\Services\Client;

use Wareon\Zipkin\Thrift\BaseTSocket;
use Wareon\Zipkin\Thrift\TTraceProtocol;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TBufferedTransport;
use Thrift\Transport\TSocket;
use Wareon\Zipkin\Facades\Zipkin;

class BaseClient
{
    public $host = '';
    public $port = '';
    protected $rpcClient = null;
    protected static $client = null;
    public static $callerId = '';

    public function __call($name, $arguments)
    {
        $newCallerId = Zipkin::clientStart($name,static::class);
        try {
            $timeout = config('zipkin.rpc_timeout', 10000);
            $socket = new BaseTSocket($this->host, $this->port);
            $socket->setRecvTimeout($timeout);
            $socket->setSendTimeout($timeout);
            $transport = new TBufferedTransport($socket);
            $protocol = new TTraceProtocol($transport, false, true);
            $protocol->setTraceInfo($newCallerId);// 传递上级spanId
            $client = new $this->rpcClient($protocol);
            $transport->open();

            if ($arguments) {
                foreach ($arguments as $k => $v) {
                    if (is_array($v)) {
                        $arguments[$k] = json_encode($v, JSON_UNESCAPED_UNICODE);
                    }
                }
            }
            // 同步方式进行交互
            $recv = call_user_func_array([$client, $name], $arguments);

            $transport->close();
            Zipkin::spanEnd();// 缓存当前span
            return json_decode($recv, true);
        } catch (\Exception $e) {
            $msg = mb_convert_encoding($e->getMessage(), "UTF-8", "GB2312");
            Zipkin::spanAnnotate($msg);
            Zipkin::spanEnd();
            return [
                'status' => 'error',
                'status_code' => 500,
                'error' => 1,
                'code' => 500,
                'message' => 'Exception: ' . $msg
            ];
        }
    }

    public static function __callStatic($name, $arguments)
    {
        $class = get_called_class();
        if (!isset(static::$client[$class])) {
            static::$client[$class] = new static();
        }

        $response = call_user_func_array([static::$client[$class], $name], $arguments);
        if (isset($response['error']) && $response['error'] == 0) {
            return [$response['code'], $response['data'] ?? [], $response];
        } else {

            if($response['code'] == 100001){

                $message = data_get($response['data'], "*.*");
                if($message)
                    $message = join(",", $message);
                else
                    $message = $response['message'];

                return [$response['code'], $message ?? [], $response];
            }else{
                return [$response['code'], $response['message'], $response];
            }
        }
    }

}