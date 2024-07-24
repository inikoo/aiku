<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 01:38:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery\Search;

use App\Actions\HydrateModel;
use App\Models\Accounting\Invoice;
use App\Models\Fulfilment\PalletDelivery;
use Illuminate\Support\Collection;

class ReindexPalletDeliverySearch extends HydrateModel
{
    public string $commandSignature = 'pallet-delivery:search {organisations?*} {--s|slugs=}';


    public function handle(PalletDelivery $palletDelivery): void
    {
        PalletDeliveryRecordSearch::run($palletDelivery);
    }

    protected function getModel(string $slug): Invoice
    {
        return PalletDelivery::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return PalletDelivery::get();
    }
}
