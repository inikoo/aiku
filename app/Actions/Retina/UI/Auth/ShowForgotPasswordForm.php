<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 21 Mar 2024 15:44:09 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\UI\Auth;

use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\Concerns\AsController;

class ShowForgotPasswordForm
{
    use AsController;

    public function handle(): Response
    {
        return Inertia::render('Auth/ForgotPasswordForm', [
            'back_label' => __('Back to login'),
            'instructions' => __('We will email you a password reset link that will allow you to choose a new one.'),
            'status' => session('status'),
        ]);
    }

    public function asController(): Response
    {
        return $this->handle();
    }
}
