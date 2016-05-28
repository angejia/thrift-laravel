<?php

namespace Angejia\Thrift\Contracts;

use Thrift\Transport\TTransport;

interface ThriftService
{
    public function register($name, $handler_class = null, $processor_class = null);

    public function process(TTransport $transport);
}
