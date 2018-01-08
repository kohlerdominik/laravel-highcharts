<?php

namespace Dokohler\Highcharts;

use Dokohler\Highcharts\Highcharts;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->bind('register-highcharts', function() {
            return new Highcharts();
        });
    }
}
