<?php

/*
 * author Arya Permana - Kirin
 * created on 09-01-2025-15h-26m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Http\Middleware;

use App\Actions\Retina\SysAdmin\ProcessRetinaWebUserRequest;
use App\Actions\SysAdmin\WithLogRequest;
use hisorange\BrowserDetect\Parser as Browser;
use Closure;
use Illuminate\Http\Request;

class LogWebUserRequestMiddleware
{
    use WithLogRequest;
    public function handle(Request $request, Closure $next)
    {
        if (!config('app.log_user_requests')) {
            return $next($request);
        }

        $routeName = $request->route()->getName();
        if (!str_starts_with($routeName, 'retina.') && !str_starts_with($routeName, 'iris.')) {
            return $next($request);
        }

        if (str_starts_with($routeName, 'iris.') &&  !$request->route()->originalParameters()) {
            return $next($request);
        }

        if ($request->route()->getName() == 'retina.logout') {
            return $next($request);
        }

        if ($request->route() instanceof \Illuminate\Routing\Route && $request->route()->getAction('uses') instanceof \Closure) {
            return $next($request);
        }

        /* @var User $user */
        $user = $request->user();
        if (!app()->runningUnitTests() && $user) {
            $parsedUserAgent = (new Browser())->parse($request->header('User-Agent'));
            $ip = $request->ip();
            ProcessRetinaWebUserRequest::dispatch(
                $user,
                now(),
                [
                    'name'      => $request->route()->getName(),
                    'arguments' => $request->route()->originalParameters(),
                    'url'       => $request->path(),
                ],
                $ip,
                $request->header('User-Agent')
            );
            if ($user->stats()->first() == null) {
                $user->stats()->create([
                    'last_device' => $parsedUserAgent->deviceType(),
                    'last_os' => $this->detectWindows11($parsedUserAgent),
                    'last_location' => json_encode($this->getLocation($ip)),
                    'last_active_at' => now()
                ]);
            } else {
                $user->stats()->update([
                    'last_device' => $parsedUserAgent->deviceType(),
                    'last_os' => $this->detectWindows11($parsedUserAgent),
                    'last_location' => json_encode($this->getLocation($ip)),
                    'last_active_at' => now()
                ]);
            }
        }

        return $next($request);
    }
}
