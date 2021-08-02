<?php
/**
 * 重写Processor读取trace
 * @category   laravel-zipkin
 * @author     wareon  <wareon@qq.com>
 * @license    project
 * @link       http://www.speedtrade.net
 * @ctime:     2021/8/1 11:15
 */


namespace Wareon\Zipkin\Thrift;


use Wareon\Zipkin\Services\Client\BaseClient;
use Thrift\Protocol\TProtocol;
use Thrift\StoredMessageProtocol;

class TTraceProcessor
{
    private $serviceProcessor;

    public function __construct($processor, $service)
    {
        $this->serviceProcessor = new $processor(new $service());
    }

    public function process(TProtocol $input, TProtocol $output)
    {
        $input->readMessageBegin($fname, $mtype, $rseqid);

        if ($input instanceof TTraceProtocol) {
            $callerId = $input->getTraceInfo();
            BaseClient::$callerId = $callerId;// 设置上级ID
        }

        if ($output instanceof TTraceProtocol) {
            $outTraceInfo = $output->getTraceInfo();
        }

        // 防止其他客户端使用非TTraceProtocol协议调用出错
        if (strpos($fname, TTraceProtocol::SEPARATOR) === false) {
            $messageName = $fname;
        } else {
            list($traceInfo, $messageName) = explode(TTraceProtocol::SEPARATOR, $fname, 2);
        }

        return $this->serviceProcessor->process(
            new StoredMessageProtocol($input, $messageName, $mtype, $rseqid),
            $output
        );
    }
}
