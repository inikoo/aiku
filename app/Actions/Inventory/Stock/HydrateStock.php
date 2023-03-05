<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:37:32 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Stock;

use App\Actions\HydrateModel;
use App\Models\Inventory\Stock;
use Illuminate\Support\Collection;

class HydrateStock extends HydrateModel
{
    public string $commandSignature = 'hydrate:stock {tenants?*} {--i|id=} ';


    public function handle(Stock $stock): void
    {
        $this->locations($stock);
        $this->quantity($stock);
    }

    public function locations(Stock $stock): void
    {
        $numberLocations = $stock->locations->count();
        $stats           = [
            'number_locations' => $numberLocations
        ];

        $stock->stats->update($stats);
    }

    public function quantity(Stock $stock): void
    {
        $stock->update([
                           'quantity' =>
                               $stock->locations->sum('pivot.quantity')
                       ]);
    }


    protected function getModel(int $id): Stock
    {
        return Stock::find($id);
    }

    protected function getAllModels(): Collection
    {
        return Stock::withTrashed()->all();
    }
}
