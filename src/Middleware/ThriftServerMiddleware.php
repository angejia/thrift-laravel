<?php

namespace Angejia\Thrift\Middleware;

use Angejia\Thrift\Contracts\ThriftServer;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Response;
use Thrift\Transport\TMemoryBuffer;

class ThriftServerMiddleware
{
    /**
     * ThriftServerMiddleware constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return Response
     */
    protected function process($request)
    {
        /* @var ThriftServer $thrift_server */
        $thrift_server = $this->app->make(ThriftServer::class);

        $transport = new TMemoryBuffer($request->getContent());

        $transport->open();
        $thrift_server->process($transport);
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