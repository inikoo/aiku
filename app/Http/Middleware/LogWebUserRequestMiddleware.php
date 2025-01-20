<?php

/*
 * author Arya Permana - Kirin
 * created on 09-01-2025-15h-26m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Http\Middleware;

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

        if (!str_starts_with($request->route()->getName(), 'retina.')) {
            return $next($request);
        }

        if ($request->route()->getName() == 'retina.logout') {
            return $next($request);
        }


        /* @var User $user */
        $user = $request->user();
        if (!app()->runningUnitTests() && $user) {
            $parsedUserAgent = (new Browser())->parse($request->header('User-Agent'));
            $ip = $request->ip();
            $user->stats()->update([
                'last_device' => json_encode(
                    [
                        'device_type' => [
                            'tooltip' => $parsedUserAgent->deviceType(),
                            'icon' => $this->getDeviceIcon($parsedUserAgent->deviceType())
                        ],
                        'platform' => [
                            'tooltip' => $this->detectWindows11($parsedUserAgent),
                            'icon'  => $this->getPlatformIcon($this->detectWindows11($parsedUserAgent))
                        ],
                    ]
                ),
                'last_location' => json_encode($this->getLocation($ip)),
                'last_active_at' => now()
            ]);
        }

        return $next($request);
    }
}
