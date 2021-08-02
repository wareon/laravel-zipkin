<?php

namespace Wareon\Zipkin\Thrift;

use Thrift\Server\TServerSocket;
use Thrift\Transport\TSocket;

class BaseTServerSocket extends TServerSocket
{
    /**
     * Implementation of accept. If not client is accepted in the given time
     *
     * @return TSocket
     */
    protected function acceptImpl()
    {
        $handle = @stream_socket_accept($this->listener_, $this->acceptTimeout_ / 1000.0);
        if (!$handle) {
            return null;
        }

        $socket = new BaseTSocket();
        $socket->setHandle($handle);

        return $socket;
    }
}
