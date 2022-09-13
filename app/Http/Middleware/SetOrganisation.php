<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SetOrganisation
{

    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {

        if(Auth::check()){
            setPermissionsTeamId($request->user()->current_ui_organisation_id);
        }

        return $next($request);
    }
}
