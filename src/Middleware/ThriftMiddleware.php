<?php

namespace Angejia\Thrift\Middleware;

use Angejia\Thrift\Contracts\ThriftService;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Response;
use Thrift\Transport\TMemoryBuffer;

class ThriftMiddleware
{
    /**
     * ThriftMiddleware constructor.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
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

            $thrift_service = $this->app(ThriftService::class);

            $transport = new TMemoryBuffer($request->getContent());

            $transport->open();
            $thrift_service->process($transport);
            $buffer = $transport->getBuffer();
            $transport->close();
            return (new Response($buffer, 200))
                ->header('Content-Type', 'application/x-thrift');
        } else {
            return $next($request);
        }
    }
}