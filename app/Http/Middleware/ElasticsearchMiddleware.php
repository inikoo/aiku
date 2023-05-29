<?php

namespace App\Http\Middleware;

use App\Actions\UserHydrateElasticsearch;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ElasticsearchMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!app()->runningUnitTests()) {
            UserHydrateElasticsearch::dispatch($request);
        }

        return $next($request);
    }
}
