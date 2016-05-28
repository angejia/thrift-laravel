<?php

namespace Angejia\Thrift;

use Angejia\Thrift\Contracts\ThriftService;
use Thrift\Protocol\TProtocol;
use Thrift\TMultiplexedProcessor;
use Thrift\Transport\TTransport;
use Illuminate\Contracts\Foundation\Application;

class ThriftServiceImpl implements ThriftService
{
    private $app;
    private $mp;
    private $protocol_class;

    /**
     * ThriftServiceImpl constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $names = $this->app['config']["thrift.names"];

        $this->mp = new TMultiplexedProcessor();
        $this->protocol_class = $this->app['config']["thrift.protocol"];

        foreach ($names as $name) {
            $this->register($name);
        }

    }

    public function register($name, $handler_class = null, $processor_class = null)
    {

        $class_name = str_replace(".", "\\", $name);
        if ($handler_class === null)
            $handler_class = $class_name . "Handler";
        if ($processor_class === null)
            $processor_class = $class_name . "Processor";

        $handler = new $handler_class();
        $processor = new $processor_class($handler);
        $this->mp->registerProcessor($name, $processor);
    }

    public function process(TTransport $transport)
    {
        /* @var TProtocol */
        $protocol = new $this->protocol_class();
        if (!$transport->isOpen())
            $transport->open();
        $this->mp->process($protocol, $protocol);
        return $transport;
    }
}