<?php

namespace App\Http\Middleware;

use App\Actions\Auth\User\LogUserRequest;
use App\Enums\Elasticsearch\ElasticsearchTypeEnum;
use Closure;
use Illuminate\Http\Request;

class LogUserRequestMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        /* @var \App\Models\Auth\User $user */
        $user = $request->user();

        if (!app()->runningUnitTests() && $user && env('USER_REQUEST_LOGGING')) {
            LogUserRequest::run(
                now(),
                [
                    'name'      => $request->route()->getName(),
                    'arguments' => $request->route()->originalParameters(),
                    'url'       => $request->path()
                ],
                $request->ip(),
                $request->header('User-Agent'),
                ElasticsearchTypeEnum::VISIT->value,
                $user,
            );

            $user->stats->update(['last_active_at' => now()]);
        }

        return $next($request);
    }
}
