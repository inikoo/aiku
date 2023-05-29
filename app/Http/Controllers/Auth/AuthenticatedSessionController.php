<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 22:09:05 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Controllers\Auth;

use App\Actions\Traits\WithElasticsearch;
use App\Actions\UserHydrateElasticsearch;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    use WithElasticsearch;

    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'status'           => session('status'),
        ]);
    }


    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        Session::put('reloadLayout', '1');

        /** @var \App\Models\Auth\User $user */
        $user   = auth()->user();
        $locale = Arr::get($user->settings, 'language');

        app()->setLocale($locale);

        return redirect()->intended(RouteServiceProvider::HOME);
    }


    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
