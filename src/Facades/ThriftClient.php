<?php

namespace Angejia\Thrift\Facades;

use Illuminate\Support\Facades\Facade;
use Angejia\Thrift\Contracts\ThriftClient as BaseThriftClient;

/**
 * @see \Angejia\Thrift\Contracts\ThriftClient
 */
class ThriftClient extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BaseThriftClient::class;
    }
}
