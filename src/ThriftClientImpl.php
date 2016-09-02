<?php

namespace Angejia\Thrift;

use Angejia\Thrift\Contracts\ThriftClient;
use Illuminate\Contracts\Config\Repository;
use Thrift\Protocol\TBinaryProtocolAccelerated;
use Thrift\Protocol\TMultiplexedProtocol;
use Thrift\Protocol\TProtocol;
use Thrift\Transport\TCurlClient;

class ThriftClientImpl implements ThriftClient
{
    private $config;
    private $protocol_class;
    private $transport_class;
    private $providers;

    /**
     * ThriftClientImpl constructor.
     *
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;

        $this->protocol_class = $this->config->get("thrift.protocol", TBinaryProtocolAccelerated::class);
        $this->transport_class = $this->config->get("thrift.transport", TCurlClient::class);

        $arr = $this->config->get('thrift.depends');
        foreach ($arr as $endpoint => $name) {
            if (empty($endpoint)) {
                throw new \InvalidArgumentException(
                    'The endpoint of '
                    . (is_array($name) ? implode($name, ', ') : $name)
                    . ' doesn\'t exist!'
                );
            }
            $info = parse_url($endpoint);
            $info = [
                'host' => $info['host'],
                'port' => $info['port']??80,
                'uri' => $info['path']??'/',
                'scheme' => $info['scheme']??'http',
            ];
            $info['port'] = intval($info['port']);

            if (is_string($name)) {
                $this->providers[$name] = $info;
            } elseif (is_array($name)) {
                foreach ($name as $name_) {
                    /** @var string $name_ */
                    $this->providers[$name_] = $info;
                }
            }
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function with($name)
    {
        $info = $this->providers[$name];
        $transport = new $this->transport_class($info['host'], $info['port'], $info['uri'], $info['scheme']);

        /** @var TProtocol $protocol */
        $protocol = new $this->protocol_class($transport);
        $protocol = new TMultiplexedProtocol($protocol, $name);

        $client_class = str_replace(".", "\\", $name) . "Client";
        $client = new $client_class($protocol);

        return $client;
    }
}
