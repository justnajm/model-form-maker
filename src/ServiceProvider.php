<?php

namespace Najm\ModelFormMaker;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->commands([
            Commands\ModelFormMaker::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return [
            Commands\ModelFormMaker::class
        ];
    }
}