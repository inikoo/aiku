<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:33:20 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\GoogleDrive;

use App\Actions\Helpers\GoogleDrive\Traits\WithGoogleDrive;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class AuthorizeClientGoogleDrive
{
    use AsAction;
    use WithGoogleDrive;

    /**
     * @throws \Exception
     */
    public function asController(Organisation $organisation): RedirectResponse
    {
        return $this->authorize($organisation);
    }
}
