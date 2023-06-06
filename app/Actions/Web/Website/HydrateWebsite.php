<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 07 Jun 2023 01:01:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Actions\HydrateModel;

;

use App\Models\Web\Website;
use Illuminate\Support\Collection;

class HydrateWebsite extends HydrateModel
{
    public string $commandSignature = 'hydrate:websites {tenants?*} {--i|id=} ';


    public function handle(Website $website): void
    {
        WebsiteHydrateWebpages::run($website);

    }


    protected function getModel(int $id): Website
    {
        return Website::find($id);
    }

    protected function getAllModels(): Collection
    {
        return Website::withTrashed()->get();
    }
}
