<?php
/**
 * 重写微服务协议类增加传输trace
 * @category   laravel-zipkin
 * @author     wareon  <wareon@qq.com>
 * @license    project
 * @link       http://www.speedtrade.net
 * @ctime:     2021/7/31 16:19
 */

namespace Wareon\Zipkin\Thrift;

use Thrift\Protocol\TBinaryProtocol;

class TTraceProtocol extends TBinaryProtocol
{
    const SEPARATOR = "~";
    private $idSeparator = ',';
    private $traceInfo = '';

    public function __construct($trans, $strictRead = false, $strictWrite = true)
    {
        parent::__construct($trans, $strictRead, $strictWrite);
    }

    public function writeMessageBegin($name, $type, $seqid)
    {
        if (!empty($this->traceInfo)){
            $name = $this->traceInfo . self::SEPARATOR . $name;// 重命名原方法名
        }
        return parent::writeMessageBegin($name, $type, $seqid);
    }

    public function readMessageBegin(&$name, &$type, &$seqid)
    {
        $result = parent::readMessageBegin($name, $type, $seqid);
        $pos = strpos($name, self::SEPARATOR);
        if ($pos !== false) {
            $this->traceInfo = substr($name, 0, $pos);// 读取trace信息
            $name = substr($name, $pos + 1);// 恢复原方法名
        }
        return $result;
    }

    public function setTraceInfo($traceInfo)
    {
        if (is_string($traceInfo)) {
            $this->traceInfo = $traceInfo;
        } elseif (is_array($traceInfo)) {
            ksort($traceInfo);
            $this->traceInfo = implode($this->idSeparator, $traceInfo);
        }
    }

    public function getTraceInfo()
    {
        if(strpos($this->traceInfo, $this->idSeparator) !== false){
            return explode($this->idSeparator, $this->traceInfo);
        } else {
            return $this->traceInfo;
        }
    }
}
