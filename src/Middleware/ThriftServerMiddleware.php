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
        $transport = new TMemoryBuffer($request->getContent());

        $transport->open();
        $this->thrift_server->process($transport);
        $buffer = $transport->getBuffer();
        $transport->close();
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
