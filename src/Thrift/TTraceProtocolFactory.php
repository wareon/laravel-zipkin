<?php
/**
 * 协议工厂类
 * @category   laravel-zipkin
 * @author     wareon  <wareon@qq.com>
 * @license    project
 * @link       http://www.speedtrade.net
 * @ctime:     2021/7/31 17:10
 */


namespace Wareon\Zipkin\Thrift;

use Thrift\Factory\TBinaryProtocolFactory;

class TTraceProtocolFactory extends TBinaryProtocolFactory
{
    private $strictRead_ = false;
    private $strictWrite_ = false;

    public function __construct($strictRead = false, $strictWrite = false)
    {
        $this->strictRead_ = $strictRead;
        $this->strictWrite_ = $strictWrite;
    }

    public function getProtocol($trans)
    {
        return new TTraceProtocol($trans, $this->strictRead_, $this->strictWrite_);
    }
}
