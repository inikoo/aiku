<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 13 Aug 2023 15:58:05 Malaysia Time, Sanur, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Public\Auth;

use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\Concerns\AsController;

class ShowPasswordResetLink
{
    use AsController;

    public function handle(): Response
    {
        return Inertia::render('Public/Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    public function asController(): Response
    {
        return $this->handle();
    }
}
