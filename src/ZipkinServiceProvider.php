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
        //
        $this->mergeConfigFrom(
            __DIR__.'/config/zipkin.php', 'database.redis.zipkin'
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
        //
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
            ]);
        }
    }

    public function provides()
    {
        return ['Zipkin'];
    }
}