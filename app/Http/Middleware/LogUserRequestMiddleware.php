<?php

namespace App\Http\Middleware;

use App\Actions\Auth\User\LogUserRequest;
use Closure;
use Illuminate\Http\Request;

class LogUserRequestMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!app()->runningUnitTests() && $user) {
            LogUserRequest::run(
                now(),
                [
                    'name'      => $request->route()->getName(),
                    'arguments' => $request->route()->originalParameters(),
                    'url'       => $request->path()
                ],
                $request->ip(),
                $request->header('User-Agent'),
                $user,
            );
        }

        return $next($request);
    }
}
