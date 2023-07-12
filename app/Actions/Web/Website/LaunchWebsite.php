<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Jun 2023 11:16:22 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateWeb;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Models\Web\Website;

class LaunchWebsite
{
    use WithActionUpdate;

    public function handle(Website $website): Website
    {
        $this->update($website, [
            'state' =>
                WebsiteStateEnum::LIVE
        ]);
        TenantHydrateWeb::dispatch(app('currentTenant'));

        return $website;
    }
}
