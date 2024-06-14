<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 14 Jun 2024 19:14:53 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Fulfilment\PalletDelivery\Hydrators\PalletDeliveryHydratePallets;
use App\Actions\Fulfilment\PalletDelivery\Hydrators\PalletDeliveryHydratePhysicalGoods;
use App\Actions\Fulfilment\PalletDelivery\Hydrators\PalletDeliveryHydrateServices;
use App\Actions\HydrateModel;
use App\Models\Fulfilment\PalletDelivery;
use Illuminate\Support\Collection;

class HydratePalletDelivery extends HydrateModel
{
    public string $commandSignature = 'pallet-delivery:hydrate {organisations?*} {--s|slugs=}';


    public function handle(PalletDelivery $palletDelivery): void
    {
        PalletDeliveryHydratePallets::run($palletDelivery);
        PalletDeliveryHydratePhysicalGoods::run($palletDelivery);
        PalletDeliveryHydrateServices::run($palletDelivery);
    }

    protected function getModel(string $slug): PalletDelivery
    {
        return PalletDelivery::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return PalletDelivery::withTrashed()->get();
    }
}
