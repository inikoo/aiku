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
        $user = $request->user();

        if (!app()->runningUnitTests() && $user) {
            UserHydrateElasticsearch::run($request, $user);
        }

        return $next($request);
    }
}
