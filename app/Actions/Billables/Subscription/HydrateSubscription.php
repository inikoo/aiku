<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 20 Nov 2024 15:22:21 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Billables\Subscription;

use App\Actions\Billables\Subscription\Hydrators\SubscriptionHydrateHistoricAssets;
use App\Actions\HydrateModel;
use App\Models\Catalogue\Shop;
use App\Models\Catalogue\Subscription;
use Illuminate\Support\Collection;

class HydrateSubscription extends HydrateModel
{
    public string $commandSignature = 'subscription:hydrate {organisations?*} {--s|slugs=} ';


    public function handle(Subscription $subscription): void
    {

        SubscriptionHydrateHistoricAssets::run($subscription);

    }


    protected function getModel(string $slug): Shop
    {
        return Shop::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Shop::withTrashed()->get();
    }
}
