<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 07 Jun 2023 01:12:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website\Hydrators;

use App\Actions\Traits\WithTenantJob;
use App\Models\Web\Website;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class WebsiteHydrateWebpages implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Website $website): void
    {
        $stats = [
            'number_webpages' => $website->webpages->count(),
        ];


        $website->webStats()->update($stats);
    }

    public function getJobUniqueId(Website $website): string
    {
        return $website->id;
    }
}
