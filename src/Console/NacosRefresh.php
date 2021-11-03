<?php

namespace Wareon\Zipkin\Console;

use alibaba\nacos\Nacos;
use alibaba\nacos\NacosConfig;
use App\Bootstrap\LoadNacosVariables;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class NacosRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nacos:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        \alibaba\nacos\NacosConfig::setSnapshotPath(base_path()."/config");

        $client = \alibaba\nacos\Nacos::init(
            env("NACOS_HOST", "http://127.0.0.1:8848"),
            null,
            env("NACOS_DATA_ID_PROJECT", null),
            env("NACOS_GROUP_ID", null),
            env("NACOS_NAMESPACE_ID", null)
        );

        $client->runOnce();

        NacosConfig::setDataId(env("NACOS_DATA_ID_COMMON", null));
        $client->runOnce();

        // 重新加载环境变量
        (new LoadNacosVariables())->bootstrap(App());
    }
}
