<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SetOrganisation
{

    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {



        if (! $request->user()->username or  !$request->user()->organisation_id) {
            return redirect('setup');
        }

        setPermissionsTeamId($request->user()->organisation_id);
        return $next($request);
    }
}
