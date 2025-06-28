<?php

namespace Flagship\Middleware;

use Closure;
use Flagship\Facades\Flagship;
use Illuminate\Http\Request;

class FlagshipMiddleware
{
    public function handle(Request $request, Closure $next, string $flag)
    {
        if (!Flagship::isEnabled($flag, $request->user())) {
            abort(404);
        }

        return $next($request);
    }
}
