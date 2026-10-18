<?php

/*
 * Author: Vika Aqordi <aqordivika@yahoo.co.id>
 * Created: Fri, 10 Jul 2026 14:00:00 Bali, Indonesia
 * Copyright (c) 2026, Vika Aqordi
*/

namespace App\Actions\Inventory\OrgStock\Json;

use App\Actions\Inventory\OrgStock\UI\GetOrgStockShowcase;
use App\Actions\OrgAction;
use App\Models\Inventory\OrgStock;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;

/**
 * Returns the stocks management showcase payload for an org stock in a warehouse,
 * so stock can be moved/managed across locations from the waiting items screen.
 */
class FetchOrgStockStocksManagement extends OrgAction
{
    public function handle(Warehouse $warehouse, OrgStock $orgStock): Collection
    {
        return GetOrgStockShowcase::run($warehouse, $orgStock); // Should takes only stocks management (not enough time)
    }

    public function jsonResponse(Collection $showcase): array
    {
        return $showcase->toArray();
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo([
            "inventory.{$this->organisation->id}.view",
            "inventory.{$this->organisation->id}.edit",
            "accounting.{$this->organisation->id}.view",
            "org-admin.{$this->organisation->id}",
            "supervisor-stocks.{$this->warehouse->id}",
            "stocks.{$this->warehouse->id}.view",
            "stocks.{$this->warehouse->id}.edit",
            "dispatching.{$this->warehouse->id}.view",
            "dispatching.{$this->warehouse->id}.edit",
            "supervisor-dispatching.{$this->warehouse->id}",
            "fulfilment.{$this->warehouse->id}.view",
            "fulfilment.{$this->warehouse->id}.edit",
        ]);
    }

    public function asController(Warehouse $warehouse, OrgStock $orgStock, ActionRequest $request): Collection
    {
        abort_unless($orgStock->organisation_id === $warehouse->organisation_id, 404);
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($warehouse, $orgStock);
    }
}
