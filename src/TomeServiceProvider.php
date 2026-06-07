<?php

namespace Tome\Tome;

use Illuminate\Support\ServiceProvider;
use Tome\Tome\Console\CheckHealthCommand;

class TomeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckHealthCommand::class,
            ]);
        }
    }
}
