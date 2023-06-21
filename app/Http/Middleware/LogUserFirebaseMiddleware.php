<?php

namespace App\Http\Middleware;

use App\Actions\Firebase\DeleteUserLogFirebase;
use App\Actions\Firebase\StoreUserLogFirebase;
use Closure;
use Illuminate\Http\Request;

class LogUserFirebaseMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if($user) {
            StoreUserLogFirebase::dispatch($user);

            if($request->route()->getName() == 'logout') {
                DeleteUserLogFirebase::dispatch($user);
            }
        }

        return $next($request);
    }
}
