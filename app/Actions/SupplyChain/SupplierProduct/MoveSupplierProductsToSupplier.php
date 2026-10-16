<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\SupplierProduct;

use App\Actions\Procurement\OrgSupplier\Hydrators\OrgSupplierHydrateOrgSupplierProducts;
use App\Actions\Procurement\OrgSupplier\StoreOrgSupplier;
use App\Actions\SupplyChain\Supplier\Hydrators\SupplierHydrateSupplierProducts;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgSupplier;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierProduct;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Moves products from a generic supplier record to the real factory of the same agent. Each product keeps its
 * id, so its purchase order lines, deliveries and history stay with it; past purchase orders stay on the
 * generic record. In every organisation that buys it, the product joins the factory's organisation supplier.
 */
class MoveSupplierProductsToSupplier
{
    use AsAction;

    /**
     * @param Collection<int, SupplierProduct> $supplierProducts
     *
     * @return array{moved: int, skipped: array<int, string>}
     */
    public function handle(Supplier $factory, Collection $supplierProducts): array
    {
        $this->assertCanMove($factory, $supplierProducts);

        $codesAtFactory = $factory->supplierProducts()->pluck('code')->map(fn (string $code) => Str::lower($code))->flip();
        [$skipped, $toMove] = $supplierProducts->partition(fn (SupplierProduct $supplierProduct) => $codesAtFactory->has(Str::lower($supplierProduct->code)));

        if ($toMove->isEmpty()) {
            throw ValidationException::withMessages([
                'supplier_products' => __(':supplier already has products with these codes, nothing was moved.', ['supplier' => $factory->code]),
            ]);
        }

        $genericSuppliers = Supplier::whereIn('id', $toMove->pluck('supplier_id')->unique())->get();
        $orgSuppliers     = collect();

        DB::transaction(function () use ($factory, $toMove, &$orgSuppliers) {
            foreach ($toMove as $supplierProduct) {
                foreach ($supplierProduct->orgSupplierProducts as $orgSupplierProduct) {
                    $orgSuppliers->push($orgSupplierProduct->orgSupplier);
                    $factoryOrgSupplier = $this->factoryOrgSupplier($factory, $orgSupplierProduct->organisation_id);
                    $orgSupplierProduct->update(['org_supplier_id' => $factoryOrgSupplier->id]);
                    $orgSuppliers->push($factoryOrgSupplier);
                }

                $supplierProduct->update(['supplier_id' => $factory->id]);
            }
        });

        foreach ($genericSuppliers->push($factory) as $supplier) {
            SupplierHydrateSupplierProducts::dispatch($supplier);
        }
        foreach ($orgSuppliers->filter()->unique('id') as $orgSupplier) {
            OrgSupplierHydrateOrgSupplierProducts::dispatch($orgSupplier);
        }

        return [
            'moved'   => $toMove->count(),
            'skipped' => $skipped->pluck('code')->values()->all(),
        ];
    }

    private function factoryOrgSupplier(Supplier $factory, int $organisationId): OrgSupplier
    {
        $orgSupplier = OrgSupplier::where('supplier_id', $factory->id)->where('organisation_id', $organisationId)->first();
        if ($orgSupplier) {
            return $orgSupplier;
        }

        $orgAgent = OrgAgent::where('agent_id', $factory->agent_id)->where('organisation_id', $organisationId)->firstOrFail();

        return StoreOrgSupplier::make()->action($orgAgent, $factory);
    }

    /**
     * @param Collection<int, SupplierProduct> $supplierProducts
     */
    private function assertCanMove(Supplier $factory, Collection $supplierProducts): void
    {
        $message = match (true) {
            $supplierProducts->isEmpty() => __('Choose the products to move.'),
            $factory->is_generic => __(':supplier is a generic record: move the products to their real factory.', ['supplier' => $factory->code]),
            !$factory->agent_id => __('Products move only between suppliers of the same agent.'),
            $supplierProducts->contains(fn (SupplierProduct $supplierProduct) => $supplierProduct->supplier_id === $factory->id) => __('Some products already belong to :supplier.', ['supplier' => $factory->code]),
            $supplierProducts->contains(fn (SupplierProduct $supplierProduct) => $supplierProduct->agent_id !== $factory->agent_id) => __('Products move only between suppliers of the same agent.'),
            $supplierProducts->contains(fn (SupplierProduct $supplierProduct) => !$supplierProduct->supplier?->is_generic) => __('Only products under a generic record can be moved.'),
            $supplierProducts->contains(fn (SupplierProduct $supplierProduct) => $supplierProduct->currency_id !== $factory->currency_id) => __(':supplier works in another currency than these products.', ['supplier' => $factory->code]),
            default => null,
        };

        if ($message) {
            throw ValidationException::withMessages(['supplier_products' => $message]);
        }
    }
}
