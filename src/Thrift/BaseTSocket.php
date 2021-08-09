<?php
/**
 * 优化socket读写
 * @category   rpc_demo
 * @author     wareon  <wareon@qq.com>
 * @license    project
 * @link       http://www.speedtrade.net
 * @ctime:     2021/5/28 15:31
 */

namespace Wareon\Zipkin\Thrift;

use Thrift\Exception\TTransportException;
use Thrift\Factory\TStringFuncFactory;
use Thrift\Transport\TSocket;

class BaseTSocket extends TSocket
{
    private $packSize = 14680; // 815559; // 用1/10大小， 块大小最大815559 + intval(815559 / 10 * 8)

    public function __construct(
        $host = 'localhost',
        $port = 9090,
        $persist = false,
        $debugHandler = null
    )
    {
        $this->host_ = $host;
        $this->port_ = $port;
        $this->persist_ = $persist;
        $this->debugHandler_ = $debugHandler ? $debugHandler : 'error_log';
    }

    /**
     * Write to the socket.
     *
     * @param string $buf The data to write
     * @throws TTransportException
     */
    public function write($buf)
    {
        $null = null;
        $write = array($this->handle_);

        // keep writing until all the data has been written
        $step = $len = 0;
        while (TStringFuncFactory::create()->strlen($buf) > 0) {
            $len = TStringFuncFactory::create()->strlen($buf);
            $step++;
            //echo " step {$step} buf {$len} \n";
            // wait for stream to become available for writing
            $writable = @stream_select(
                $null,
                $write,
                $null,
                $this->sendTimeoutSec_,
                $this->sendTimeoutUsec_
            );
            if ($writable > 0) {

                // write buffer to stream
                $written = fwrite($this->handle_, $buf, $this->packSize);

                if ($written === -1 || $written === false) {
                    throw new TTransportException(
                        'TSocket1: Could not write ' . TStringFuncFactory::create()->strlen($buf) . ' bytes ' .
                        $this->host_ . ':' . $this->port_
                    );
                }
                // determine how much of the buffer is left to write
                $buf = TStringFuncFactory::create()->substr($buf, $written);
            } elseif ($writable === 0) {
                throw new TTransportException(
                    'TSocket2: timed out writing ' . TStringFuncFactory::create()->strlen($buf) . ' bytes from ' .
                    $this->host_ . ':' . $this->port_
                );
            } else {
                throw new TTransportException(
                    'TSocket3: Could not write ' . TStringFuncFactory::create()->strlen($buf) . ' bytes ' .
                    $this->host_ . ':' . $this->port_
                );
            }
        }
        //echo " Write end:  step {$step} buf {$len} \n\n";
    }

    public function read($len)
    {
        $null = null;
        $read = array($this->handle_);
        $readable = @stream_select(
            $read,
            $null,
            $null,
            $this->recvTimeoutSec_,
            $this->recvTimeoutUsec_
        );
        $do = $maxRead = floor($len / $this->packSize);
        if ($readable > 0) {
            $dataAll = '';
            while ($len > 0 && $do >= 0) {
                if ($do == 0) {
                    $size = $len % $this->packSize;
                } else {
                    $size = $this->packSize;
                }

                $data = fread($this->handle_, $size);
                if ($data === false) {
                    throw new TTransportException('TSocket: Could not read ' . $len . ' bytes from ' .
                        $this->host_ . ':' . $this->port_);
                } elseif ($data == '' && feof($this->handle_)) {
                    throw new TTransportException('TSocket read 0 bytes');
                }
                $dataAll .= $data;
                $do--;
            }

            return $dataAll;
        } elseif ($readable === 0) {
            throw new TTransportException('TSocket: timed out reading ' . $do . '~' . $len . ' bytes from ' .
                $this->host_ . ':' . $this->port_);
        } else {
            throw new TTransportException('TSocket: Could not read ' . $len . ' bytes from ' .
                $this->host_ . ':' . $this->port_);
        }
    }

    public function getHandle(){
        return $this->handle_;
    }
}
