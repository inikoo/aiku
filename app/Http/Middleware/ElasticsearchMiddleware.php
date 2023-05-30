<?php

namespace App\Http\Middleware;

use App\Actions\UserHydrateElasticsearch;
use Closure;
use Illuminate\Http\Request;

class ElasticsearchMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!app()->runningUnitTests()) {
            $user = $request->user();
            UserHydrateElasticsearch::run($request, $user);
        }

        return $next($request);
    }
}
