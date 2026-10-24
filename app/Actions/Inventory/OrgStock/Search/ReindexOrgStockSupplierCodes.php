<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Oct 2026 16:55:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock\Search;

use App\Models\Inventory\OrgStock;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;
use Typesense\Client;

class ReindexOrgStockSupplierCodes
{
    use AsAction;

    public string $commandSignature = 'reindex_search:org_stock_supplier_codes';

    public string $commandDescription = 'Add the supplier_codes field to the org stocks search collection and reindex the org stocks linked to a supplier product';

    public function handle(?Command $command = null): int
    {
        $client     = new Client(config('scout.typesense.client-settings'));
        $collection = (new OrgStock())->searchableAs();

        $fields = collect($client->collections[$collection]->retrieve()['fields'])->pluck('name');
        if ($fields->contains('supplier_codes')) {
            $command?->info('supplier_codes field already in '.$collection);
        } else {
            $client->collections[$collection]->update([
                'fields' => [['name' => 'supplier_codes', 'type' => 'string', 'optional' => true]],
            ]);
            $command?->info('supplier_codes field added to '.$collection);
        }

        $count = 0;
        OrgStock::whereHas('orgSupplierProducts')->chunkById(500, function ($orgStocks) use (&$count) {
            $orgStocks->first()->searchableUsing()->update($orgStocks);
            $count += $orgStocks->count();
        });
        $command?->info("Org stocks reindexed: $count");

        return $count;
    }

    public function asCommand(Command $command): int
    {
        $this->handle($command);

        return 0;
    }
}
