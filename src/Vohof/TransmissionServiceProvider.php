<?php namespace Vohof;

use Illuminate\Support\ServiceProvider;

class TransmissionServiceProvider extends ServiceProvider {

    protected $defer = true;

    public function register()
    {
        $this->app->singleton('transmission', function ($app) {
            return new Transmission(config('transmission'));
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('transmission.php')
        ], 'config');
    }

    public function provides()
    {
        return array('transmission');
    }
}
