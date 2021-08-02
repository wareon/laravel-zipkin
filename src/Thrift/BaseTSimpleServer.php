<?php
/**
 * 重写Server类
 * @category   rpc_demo
 * @author     wareon  <wareon@qq.com>
 * @license    project
 * @link       http://www.speedtrade.net
 * @ctime:     2021/5/28 15:31
 */


namespace Wareon\Zipkin\Thrift;


use Thrift\Exception\TTransportException;
use Thrift\Server\TSimpleServer;

class BaseTSimpleServer extends TSimpleServer
{
    /**
     * Flag for the main serving loop
     *
     * @var bool
     */
    private $stop_ = false;

    public function serve()
    {
        $this->transport_->listen();

        while (!$this->stop_) {
            try {
                $transport = $this->transport_->accept();

                if ($transport != null) {
                    $inputTransport = $this->inputTransportFactory_->getTransport($transport);
                    $outputTransport = $this->outputTransportFactory_->getTransport($transport);
                    $inputProtocol = $this->inputProtocolFactory_->getProtocol($inputTransport);
                    $outputProtocol = $this->outputProtocolFactory_->getProtocol($outputTransport);
                    while ($this->processor_->process($inputProtocol, $outputProtocol)) {
                    }
                }
            } catch (TTransportException $e) {
                $date = date("Y-m-d H:i:s");
                $mtime = microtime(true);
                echo "[{$date} - {$mtime}] TTransportException: \n";
                //echo $e->getMessage();
                //echo $e->getTraceAsString();
                echo "End exit \n";
            }
        }
    }
}
