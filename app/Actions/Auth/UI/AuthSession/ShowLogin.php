<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Jun 2023 11:58:09 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\UI\AuthSession;

use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\Concerns\AsController;

class ShowLogin
{
    use AsController;


    public function handle(): Response
    {
        return Inertia::render('Auth/Login', [
            'status'           => session('status'),
        ]);
    }

}
