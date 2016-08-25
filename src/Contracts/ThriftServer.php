<?php

namespace Angejia\Thrift\Contracts;

use Thrift\Transport\TTransport;

interface ThriftServer
{
    public function register($name, $handler_class = null, $processor_class = null);

    public function process(TTransport $input_trans, TTransport $output_trans);
}
