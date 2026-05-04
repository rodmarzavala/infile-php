<?php

declare(strict_types=1);

namespace InfilePhp\Laravel\Studio\Http\Middleware;

use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Authorize
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response|mixed
     */
    public function handle($request, $next)
    {
        if (app()->environment('local') || app()->runningUnitTests()) {
            return $next($request);
        }

        if (Gate::has('viewFelStudio') && Gate::check('viewFelStudio', [$request->user()])) {
            return $next($request);
        }

        abort(403, 'This action is unauthorized. Define a viewFelStudio gate to allow access.');
    }
}
