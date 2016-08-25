<?php

namespace Angejia\Thrift\Middleware;

use Angejia\Thrift\Contracts\ThriftServer;
use Closure;
use Illuminate\Http\Response;
use Thrift\Transport\TMemoryBuffer;

class ThriftServerMiddleware
{
    /**
     * ThriftServerMiddleware constructor.
     * @param ThriftServer $thrift_server
     */
    public function __construct(ThriftServer $thrift_server)
    {
        $this->thrift_server = $thrift_server;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return Response
     */
    protected function process($request)
    {
        $input_trans = new TMemoryBuffer($request->getContent());
        $output_trans = new TMemoryBuffer();

        $input_trans->open();
        $this->thrift_server->process($input_trans, $output_trans);
        $buffer = $output_trans->getBuffer();
        $output_trans->close();
        return (new Response($buffer, 200))
            ->header('Content-Type', 'application/x-thrift');
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->is('rpc') && 'application/x-thrift' == $request->header('CONTENT_TYPE')) {
            return $this->process($request);
        } else {
            return $next($request);
        }
    }
}
