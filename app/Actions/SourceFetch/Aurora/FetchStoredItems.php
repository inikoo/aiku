<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Jul 2023 12:19:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Fulfilment\StoredItem\StoreStoredItem;
use App\Actions\Fulfilment\StoredItem\UpdateStoredItem;
use App\Models\Fulfilment\StoredItem;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchStoredItems extends FetchAction
{
    public string $commandSignature = 'fetch:stored-items {tenants?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?StoredItem
    {
        if ($storedItemData = $tenantSource->fetchStoredItem($tenantSourceId)) {
            if ($storedItem = StoredItem::withTrashed()->where('source_id', $storedItemData['storedItem']['source_id'])
                ->first()) {
                $storedItem = UpdateStoredItem::run(
                    storedItem: $storedItem,
                    modelData: $storedItemData['storedItem']
                );
            } else {
                $storedItem = StoreStoredItem::run(
                    customer: $storedItemData['customer'],
                    modelData: $storedItemData['storedItem'],
                );
            }


            return $storedItem;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Fulfilment Asset Dimension')
            ->select('Fulfilment Asset Key as source_id')
            ->orderBy('source_id')
            ->when(app()->environment('testing'), function ($query) {
                return $query->limit(20);
            });
    }


    public function count(): ?int
    {
        return DB::connection('aurora')->table('Fulfilment Asset Dimension')->count();
    }
}
