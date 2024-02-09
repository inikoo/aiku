<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 09 Feb 2024 10:24:53 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\WebUser\UI;

use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\Concerns\AsController;

class ShowResetWebUserPassword
{
    use AsController;

    public function handle(): Response
    {
        return Inertia::render('Auth/ResetWebUserPassword');
    }

}
