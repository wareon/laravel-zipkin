<?php
/**
 * 把缓存到REDIS中的zipkin日志消费到zipkin server
 * @category   laravel-zipkin
 * @author     wareon  <wareon@qq.com>
 * @license    project
 * @link       http://www.speedtrade.net
 * @ctime:     2021/7/21 11:46
 */

namespace Wareon\Zipkin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Zipkin\Reporters\Http\CurlFactory;

class ConsumeZipkinLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consume:zipkin_log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume REDIS zipkin log.';

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
        $config = config('zipkin');
        $url = config('zipkin.endpoint_url', 'http://localhost:9411/api/v2/spans');
        $options = [
            'endpoint_url' => $url,
        ];
        $clientFactory = CurlFactory::create();
        $client = $clientFactory->build($options);
        $key = $config['key'] ?? 'ZIPKIN:LOG';
        $this->info('Starting consume log.');
        do {
            $json = Redis::connection('zipkin')->lpop($key);
            if (!empty($json)){
                $client($json);
                $array = json_decode($json, true);
                if(is_array($array)) {
                    foreach ($array as $item){
                        $parentId = $item['parentId'] ?? '----------------';
                        $this->info("{$item['id']}\t\t{$item['traceId']}\t\t{$parentId}\t\t{$item['name']}");
                    }
                }
            }
        } while (!empty($json));
        $this->info('Consume log end.');
    }


}
