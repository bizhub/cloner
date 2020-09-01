<?php

namespace Bizhub\Cloner;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cloner', function(){
            return new Cloner;
        });
    }
}
