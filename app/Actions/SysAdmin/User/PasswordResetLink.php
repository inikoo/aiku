<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 21 Mar 2024 15:44:04 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;

class PasswordResetLink
{
    use AsController;

    public function handle(ActionRequest $request): void
    {
        $credentials = $request->only('email');

        dispatch(fn () => Password::broker('users')->sendResetLink($credentials))->afterResponse();
    }

    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    public function asController(ActionRequest $request): void
    {
        $request->validate();

        $this->handle($request);
    }
}
