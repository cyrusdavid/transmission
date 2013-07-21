<?php namespace Vohof;

use Illuminate\Support\ServiceProvider;

class TransmissionServiceProvider extends ServiceProvider {

    protected $defer = false;

    public function register()
    {
        $this->app['transmission'] = $this->app->share(function($app)
        {
            $config = array(
                'host' => $app['config']->get('transmission::host'),
                'endpoint' => $app['config']->get('transmission::endpoint')
            );

            return new Transmission($config);
        });
    }

    public function boot()
    {
        $this->package('transmission', 'transmission', realpath(__DIR__. '/../'));
    }

    public function provides()
    {
        return array('transmission');
    }
}
