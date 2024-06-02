<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 09:00:59 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Subscription;

use App\Actions\Catalogue\Subscription\Hydrators\SubscriptionHydrateHistoricAssets;
use App\Actions\HydrateModel;
use App\Models\Catalogue\Subscription;
use App\Models\Catalogue\Shop;
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
