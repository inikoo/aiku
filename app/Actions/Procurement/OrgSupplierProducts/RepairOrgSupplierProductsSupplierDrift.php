<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 01 Sep 2026, Sheffield, UK
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplierProducts;

use App\Actions\Procurement\OrgSupplier\Hydrators\OrgSupplierHydrateOrgSupplierProducts;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrgSupplierProducts;
use App\Enums\GoodsIn\StockDelivery\StockDeliveryStateEnum;
use App\Enums\Procurement\OrgSupplierProduct\OrgSupplierProductStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\GoodsIn\StockDeliveryItem;
use App\Models\Inventory\OrgStockHasOrgSupplierProduct;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\Procurement\PurchaseOrderTransaction;
use App\Models\Procurement\ShoppingListItem;
use App\Models\SysAdmin\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class RepairOrgSupplierProductsSupplierDrift
{
    use AsAction;

    public string $commandSignature = 'repair:org_supplier_products_supplier_drift {--fix : Apply the repointing and merge rows whose twin exists, otherwise dry run}';

    /**
     * @return array{repointed: int, collisions: int, merged_deleted: int, merged_discontinued: int, unfixable: int}
     */
    public function handle(bool $fix = false): array
    {
        $repointed          = 0;
        $collisions         = 0;
        $mergedDeleted      = 0;
        $mergedDiscontinued = 0;
        $unfixable          = 0;

        $touchedOrgSuppliers = [];
        $touchedOrganisations = [];

        foreach ($this->getDriftedQuery()->cursor() as $orgSupplierProduct) {
            $correctOrgSuppliers = OrgSupplier::where('organisation_id', $orgSupplierProduct->organisation_id)
                ->where('supplier_id', $orgSupplierProduct->supplierProduct->supplier_id)
                ->get();

            if ($correctOrgSuppliers->count() != 1) {
                $unfixable++;
                continue;
            }

            $correctOrgSupplier = $correctOrgSuppliers->first();

            $twin = OrgSupplierProduct::where('org_supplier_id', $correctOrgSupplier->id)
                ->where('supplier_product_id', $orgSupplierProduct->supplier_product_id)
                ->first();

            if ($twin) {
                $collisions++;

                if ($fix) {
                    $touchedOrgSuppliers[$orgSupplierProduct->org_supplier_id]  = $orgSupplierProduct->org_supplier_id;
                    $touchedOrgSuppliers[$twin->org_supplier_id]                = $twin->org_supplier_id;
                    $touchedOrganisations[$orgSupplierProduct->organisation_id] = $orgSupplierProduct->organisation_id;

                    if ($this->mergeIntoTwin($orgSupplierProduct, $twin)) {
                        $mergedDeleted++;
                    } else {
                        $mergedDiscontinued++;
                    }
                }

                continue;
            }

            if ($fix) {
                $touchedOrgSuppliers[$orgSupplierProduct->org_supplier_id]  = $orgSupplierProduct->org_supplier_id;
                $touchedOrgSuppliers[$correctOrgSupplier->id]               = $correctOrgSupplier->id;
                $touchedOrganisations[$orgSupplierProduct->organisation_id] = $orgSupplierProduct->organisation_id;

                $orgSupplierProduct->update([
                    'org_supplier_id' => $correctOrgSupplier->id,
                    'org_agent_id'    => $correctOrgSupplier->org_agent_id,
                ]);
            }

            $repointed++;
        }

        foreach (OrgSupplier::whereIn('id', $touchedOrgSuppliers)->get() as $orgSupplier) {
            OrgSupplierHydrateOrgSupplierProducts::run($orgSupplier);
        }

        foreach (Organisation::whereIn('id', $touchedOrganisations)->get() as $organisation) {
            OrganisationHydrateOrgSupplierProducts::run($organisation);
        }

        return [
            'repointed'           => $repointed,
            'collisions'          => $collisions,
            'merged_deleted'      => $mergedDeleted,
            'merged_discontinued' => $mergedDiscontinued,
            'unfixable'           => $unfixable,
        ];
    }

    public function mergeIntoTwin(OrgSupplierProduct $drifted, OrgSupplierProduct $twin): bool
    {
        return DB::transaction(function () use ($drifted, $twin) {
            $twinOrgStockIds = OrgStockHasOrgSupplierProduct::where('org_supplier_product_id', $twin->id)->pluck('org_stock_id')->all();

            OrgStockHasOrgSupplierProduct::where('org_supplier_product_id', $drifted->id)
                ->whereIn('org_stock_id', $twinOrgStockIds)
                ->delete();
            OrgStockHasOrgSupplierProduct::where('org_supplier_product_id', $drifted->id)
                ->update(['org_supplier_product_id' => $twin->id]);

            PurchaseOrderTransaction::where('org_supplier_product_id', $drifted->id)
                ->whereHas('purchaseOrder', fn ($query) => $query->whereIn('state', [
                    PurchaseOrderStateEnum::IN_PROCESS,
                    PurchaseOrderStateEnum::SUBMITTED,
                    PurchaseOrderStateEnum::CONFIRMED,
                ]))
                ->update(['org_supplier_product_id' => $twin->id]);

            StockDeliveryItem::where('org_supplier_product_id', $drifted->id)
                ->whereHas('stockDelivery', fn ($query) => $query->whereNotIn('state', [
                    StockDeliveryStateEnum::PLACED,
                    StockDeliveryStateEnum::CANCELLED,
                    StockDeliveryStateEnum::NOT_RECEIVED,
                ]))
                ->update(['org_supplier_product_id' => $twin->id]);

            $hasHistory = PurchaseOrderTransaction::where('org_supplier_product_id', $drifted->id)->exists()
                || StockDeliveryItem::where('org_supplier_product_id', $drifted->id)->exists()
                || ShoppingListItem::where('org_supplier_product_id', $drifted->id)->exists();

            if ($hasHistory) {
                $drifted->update(['state' => OrgSupplierProductStateEnum::DISCONTINUED]);

                return false;
            }

            $drifted->stats()->delete();
            $drifted->delete();

            return true;
        });
    }

    public function getDriftedQuery()
    {
        return OrgSupplierProduct::with('supplierProduct')
            ->join('supplier_products', 'supplier_products.id', '=', 'org_supplier_products.supplier_product_id')
            ->join('org_suppliers', 'org_suppliers.id', '=', 'org_supplier_products.org_supplier_id')
            ->whereColumn('supplier_products.supplier_id', '!=', 'org_suppliers.supplier_id')
            ->select('org_supplier_products.*');
    }

    public function asCommand(Command $command): int
    {
        $fix = (bool)$command->option('fix');

        $result = $this->handle($fix);

        $command->info($fix ? 'Repointed:' : 'Would repoint:');
        $command->table(
            ['repointed', 'collisions (twin exists)', 'merged and deleted', 'merged and discontinued', 'unfixable (no single correct org supplier)'],
            [[$result['repointed'], $result['collisions'], $result['merged_deleted'], $result['merged_discontinued'], $result['unfixable']]]
        );

        return 0;
    }
}
