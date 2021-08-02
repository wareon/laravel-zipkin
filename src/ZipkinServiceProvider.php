<?php

namespace Wareon\Zipkin;

use Illuminate\Support\ServiceProvider;

class ZipkinServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 加载配置项到redis
        $this->mergeConfigFrom(
            __DIR__.'/config/zipkin_redis.php', 'database.redis.zipkin'
        );
        // Single Class
        $this->app->singleton('Zipkin', function ($app) {
            return new Zipkin();
        });
        $this->registerCommands();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/zipkin.php' => config_path('zipkin.php'), // publish to laravel config dir
        ]);
    }

    /**
     * Register the Horizon Artisan commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\ConsumeZipkinLog::class,
                Console\RpcServerStart::class,
            ]);
        }
    }

    public function provides()
    {
        return ['Zipkin'];
    }
}
