<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 01:38:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn\Search;

use App\Actions\HydrateModel;
use App\Models\Accounting\Invoice;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Support\Collection;

class ReindexPalletReturnSearch extends HydrateModel
{
    public string $commandSignature = 'pallet-return:search {organisations?*} {--s|slugs=}';


    public function handle(PalletReturn $palletReturn): void
    {
        PalletReturnRecordSearch::run($palletReturn);
    }

    protected function getModel(string $slug): Invoice
    {
        return PalletReturn::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return PalletReturn::withTrashed()->get();
    }
}
