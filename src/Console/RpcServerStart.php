<?php

namespace Wareon\Zipkin\Console;

use Wareon\Zipkin\Thrift\BaseTForkingServer;
use Wareon\Zipkin\Thrift\BaseTServerSocket;
use Wareon\Zipkin\Thrift\BaseTSimpleServer;
use Wareon\Zipkin\Thrift\TTraceProcessor;
use Wareon\Zipkin\Thrift\TTraceProtocolFactory;
use Wareon\Zipkin\Services\Server\DemoService;
use Wareon\Zipkin\Thrift\Demo\DemoProcessor;
use Illuminate\Console\Command;
use Thrift\Exception\TApplicationException;
use Thrift\Exception\TException;
use Thrift\Exception\TProtocolException;
use Thrift\Exception\TTransportException;
use Thrift\Factory\TTransportFactory;

class RpcServerStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rpc:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start RPC Server';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->getService();
    }

    public function getService()
    {
        $this->callSilent('nacos:refresh');

        try {
            
            $name = config('zipkin.rpc_name', 'Laravel zipkin RPC');
            $processorClass = config('zipkin.processor_class');
            $serviceClass = config('zipkin.service_class');
            $host = config('zipkin.rpc_host', '127.0.0.1');
            $port = config('zipkin.rpc_port', '8911');
            $timeout = config('zipkin.rpc_timeout', 10000);

            $processor = new TTraceProcessor($processorClass, $serviceClass);
            $tFactory = new TTransportFactory();
            $pFactory = new TTraceProtocolFactory();

            $transport = new BaseTServerSocket($host, $port);
            $transport->setAcceptTimeout($timeout);

            if (strpos(PHP_OS, "WIN") !== false) {
                $server = new BaseTSimpleServer($processor, $transport, $tFactory, $tFactory, $pFactory, $pFactory);
            } else {
                $server = new BaseTForkingServer($processor, $transport, $tFactory, $tFactory, $pFactory, $pFactory);
            }

            $this->info(date("Y-m-d H:i:s") . " Start {$name} Server Success [{$host}:{$port}]！");
            $server->serve();

        } catch (TApplicationException $e) {
            $this->error(date("Y-m-d H:i:s") . " Start {$name} Server Error！TApplicationException：");
            $this->error($e->getMessage());
        } catch (TTransportException $e) {
            $this->error(date("Y-m-d H:i:s") . " Start {$name} Server Error！TTransportException：");
            $this->error($e->getMessage());
        } catch (TProtocolException $e) {
            $this->error(date("Y-m-d H:i:s") . " Start {$name} Server Error！TProtocolException：");
            $this->error($e->getMessage());
        } catch (TException $e) {
            $this->error(date("Y-m-d H:i:s") . " Start {$name} Server Error！TException：");
            $this->error($e->getMessage());
        } catch (\Exception $e) {
            $this->error(date("Y-m-d H:i:s") . " Start {$name} Server Error！Exception：");
            $this->error($e->getMessage());
        }
    }

}
