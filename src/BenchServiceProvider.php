<?php

namespace Metallizzer\Bench;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
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
