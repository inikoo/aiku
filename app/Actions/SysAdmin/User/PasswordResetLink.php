<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 21 Mar 2024 15:44:04 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;

class PasswordResetLink
{
    use AsController;

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(ActionRequest $request): void
    {
        $status = Password::broker('users')->sendResetLink(
            $request->only('email')
        );

        if($status === Password::INVALID_USER) {
            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        }
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
            'email' => ['required', 'email', 'exists:users'],
        ];
    }

    public function asController(ActionRequest $request): void
    {
        $request->validate();

        $this->handle($request);
    }
}
