<?php

namespace Angejia\Thrift;

use Angejia\Thrift\Contracts\ThriftServer;
use Illuminate\Contracts\Config\Repository;
use Thrift\Protocol\TBinaryProtocolAccelerated;
use Thrift\Protocol\TProtocol;
use Thrift\TMultiplexedProcessor;
use Thrift\Transport\TTransport;

class ThriftServerImpl implements ThriftServer
{
    /**
     * @var Repository
     */
    private $config;

    /**
     * 总的 Processor
     *
     * @var TMultiplexedProcessor
     */
    private $mprocessor;

    /**
     * @var string
     */
    private $protocol_class;

    /**
     * ThriftServiceImpl constructor.
     *
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;

        $this->protocol_class = $this->config->get("thrift.protocol", TBinaryProtocolAccelerated::class);
    }

    /**
     *  向 $mprocessor注册所有配置文件中的服务
     */
    protected function registerAll()
    {
        if (is_null($this->mprocessor)) {
            $providers = $this->config->get("thrift.providers");
            $this->mprocessor = new TMultiplexedProcessor();
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
    }

    /**
     * 注册 name 的 handler 和 processor
     *
     * @param $name
     * @param string|null $handler_class
     * @param string|null $processor_class
     */
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

    /**
     * 处理 RPC 请求
     *
     * @param TTransport $transport
     * @return TTransport
     */
    public function process(TTransport $transport)
    {
        $this->registerAll();

        /* @var TProtocol */
        $protocol = new $this->protocol_class($transport);
        if (!$transport->isOpen())
            $transport->open();
        $this->mprocessor->process($protocol, $protocol);
        return $transport;
    }
}
