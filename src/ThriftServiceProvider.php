<?php

namespace Angejia\Thrift;

use Illuminate\Support\ServiceProvider;

class ThriftServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Angejia\Thrift\Contracts\ThriftServer', 'Angejia\Thrift\ThriftServerImpl');
        $this->app->singleton('Angejia\Thrift\Contracts\ThriftClient', 'Angejia\Thrift\ThriftClientImpl');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['Angejia\Thrift\Contracts\ThriftServer', 'Angejia\Thrift\Contracts\ThriftClient'];
    }
}
