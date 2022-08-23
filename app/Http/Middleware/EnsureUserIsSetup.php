<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnsureUserIsSetup
{

    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {
        if (! $request->user()->username or  $request->user()->number_organisations==0) {
            return redirect('setup');
        }

        return $next($request);
    }
}
