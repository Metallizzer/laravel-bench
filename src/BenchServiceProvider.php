<?php

namespace Metallizzer\Bench;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Metallizzer\Bench\Console\BenchMakeCommand;
use Metallizzer\Bench\Console\BenchRunCommand;
use Metallizzer\Bench\Http\Middleware\Authorize;

class BenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/config/bench.php', 'bench'
        );

        $this->app->singleton('bench', function ($app) {
            return new Bench($app);
        });

        if ($this->app->runningInConsole()) {
            $this->app->bind('command.bench:run', BenchRunCommand::class);
            $this->app->bind('command.bench:make', BenchMakeCommand::class);

            $this->commands(['command.bench:run']);
            $this->commands(['command.bench:make']);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $this->publishes([
            dirname(__DIR__).'/config/bench.php' => config_path('bench.php'),
        ], 'config');
        $this->publishes([
            dirname(__DIR__).'/public' => public_path('vendor/bench'),
        ], 'bench-assets');

        $this->loadViewsFrom(dirname(__DIR__).'/resources/views', 'bench');

        $this->registerRoutes();
    }

    /**
     * Register the package routes.
     */
    protected function registerRoutes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware([Authorize::class, 'web'])
            ->prefix(config('bench.path'))
            ->name('bench.')
            ->namespace('Metallizzer\\Bench\\Http\\Controllers')
            ->group(function () {
                Route::get('/', 'BenchController@index')->name('index');
                Route::post('/', 'BenchController@run')->name('run');
            });
    }
}
