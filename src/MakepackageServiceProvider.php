<?php

namespace Ollywarren\Makepackage;

use Illuminate\Support\ServiceProvider;
use Ollywarren\Makepackage\Classes\CreateComposerPackage;

class MakepackageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateComposerPackage::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}