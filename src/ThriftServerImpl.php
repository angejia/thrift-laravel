<?php

namespace Angejia\Thrift;

use Angejia\Thrift\Contracts\ThriftServer;
use Thrift\Protocol\TBinaryProtocolAccelerated;
use Thrift\Protocol\TProtocol;
use Thrift\TMultiplexedProcessor;
use Thrift\Transport\TTransport;
use Illuminate\Contracts\Config\Repository;

class ThriftServerImpl implements ThriftServer
{
    private $config;
    private $mprocessor;
    private $protocol_class;

    /**
     * ThriftServiceImpl constructor.
     *
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
        $providers = $this->config->get("thrift.providers");

        $this->mprocessor = new TMultiplexedProcessor();
        $this->protocol_class = $this->config->get("thrift.protocol", TBinaryProtocolAccelerated::class);

        foreach ($providers as $provider) {
            if (is_string($provider)) {
                $this->register($provider);
            } elseif (is_array($provider)) {
                $this->register($provider[0], $provider[1]);
            } else {
                throw new \InvalidArgumentException("provider must be name or array. now it's " . var_export($provider));
            }
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
        $this->mprocessor->registerProcessor($name, $processor);
    }

    public function process(TTransport $transport)
    {
        /* @var TProtocol */
        $protocol = new $this->protocol_class($transport);
        if (!$transport->isOpen())
            $transport->open();
        $this->mprocessor->process($protocol, $protocol);
        return $transport;
    }
}
