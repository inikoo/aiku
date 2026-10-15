<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 May 2024 20:51:57 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplierProducts;

use App\Actions\OrgAction;
use App\Actions\Procurement\OrgAgent\Hydrators\OrgAgentHydrateOrgSupplierProducts;
use App\Actions\Procurement\OrgSupplier\Hydrators\OrgSupplierHydrateOrgSupplierProducts;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrgSupplierProducts;
use App\Enums\Procurement\OrgSupplierProduct\OrgSupplierProductStateEnum;
use App\Enums\SupplyChain\SupplierProduct\SupplierProductStateEnum;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\SupplyChain\SupplierProduct;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Lorisleiva\Actions\ActionRequest;

class StoreOrgSupplierProduct extends OrgAction
{
    public bool $skipHydrators = false;

    public function handle(OrgSupplier $orgSupplier, SupplierProduct $supplierProduct, $modelData = []): OrgSupplierProduct
    {
        if ($supplierProduct->supplier_id != $orgSupplier->supplier_id) {
            throw new InvalidArgumentException("Supplier product $supplierProduct->id belongs to supplier $supplierProduct->supplier_id, not to org supplier $orgSupplier->id");
        }

        data_set($modelData, 'group_id', $orgSupplier->group_id);
        data_set($modelData, 'organisation_id', $orgSupplier->organisation_id);


        data_set($modelData, 'org_agent_id', $orgSupplier->org_agent_id);
        data_set($modelData, 'org_supplier_id', $orgSupplier->id);

        $state = match ($supplierProduct->state) {
            SupplierProductStateEnum::DISCONTINUING => OrgSupplierProductStateEnum::DISCONTINUING,
            SupplierProductStateEnum::DISCONTINUED  => OrgSupplierProductStateEnum::DISCONTINUED,
            default                                 => OrgSupplierProductStateEnum::ACTIVE
        };
        data_set($modelData, 'state', $state);
        data_set($modelData, 'is_available', $supplierProduct->is_available);

        $this->repointDriftedOrgSupplierProduct($orgSupplier, $supplierProduct);

        /** @var OrgSupplierProduct $orgSupplierProduct */
        $orgSupplierProduct = $supplierProduct->orgSupplierProducts()->firstOrCreate(['org_supplier_id' => $orgSupplier->id], $modelData);

        if (!$orgSupplierProduct->wasRecentlyCreated) {
            if (!$orgSupplierProduct->source_id && Arr::get($modelData, 'source_id')) {
                $orgSupplierProduct->update(['source_id' => $modelData['source_id']]);
            }

            return $orgSupplierProduct;
        }

        $orgSupplierProduct->stats()->create();

        if (!$this->skipHydrators) {
            OrganisationHydrateOrgSupplierProducts::dispatch($orgSupplier->organisation)->delay($this->hydratorsDelay);
            OrgSupplierHydrateOrgSupplierProducts::dispatch($orgSupplier)->delay($this->hydratorsDelay);
            if ($orgSupplierProduct->org_agent_id) {
                OrgAgentHydrateOrgSupplierProducts::dispatch($orgSupplierProduct->orgAgent)->delay($this->hydratorsDelay);
            }
        }


        return $orgSupplierProduct;
    }

    protected function repointDriftedOrgSupplierProduct(OrgSupplier $orgSupplier, SupplierProduct $supplierProduct): void
    {
        if ($supplierProduct->orgSupplierProducts()->where('org_supplier_id', $orgSupplier->id)->exists()) {
            return;
        }

        /** @var OrgSupplierProduct $driftedOrgSupplierProduct */
        $driftedOrgSupplierProduct = $supplierProduct->orgSupplierProducts()
            ->where('org_supplier_products.organisation_id', $orgSupplier->organisation_id)
            ->whereHas('orgSupplier', fn ($query) => $query->where('supplier_id', '!=', $supplierProduct->supplier_id))
            ->first();

        if (!$driftedOrgSupplierProduct) {
            return;
        }

        $previousOrgSupplier = $driftedOrgSupplierProduct->orgSupplier;

        $driftedOrgSupplierProduct->update([
            'org_supplier_id' => $orgSupplier->id,
            'org_agent_id'    => $orgSupplier->org_agent_id,
        ]);

        if (!$this->skipHydrators && $previousOrgSupplier) {
            OrgSupplierHydrateOrgSupplierProducts::dispatch($previousOrgSupplier)->delay($this->hydratorsDelay);
        }
    }


    public function rules(ActionRequest $request): array
    {
        return [
            'source_id' => 'sometimes|nullable|string|max:64',
        ];
    }

    public function action(OrgSupplier $orgSupplier, SupplierProduct $supplierProduct, $modelData = [], $hydratorsDelay = 0, bool $skipHydrators = false): OrgSupplierProduct
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->skipHydrators  = $skipHydrators;
        $this->initialisation($orgSupplier->organisation, $modelData);

        return $this->handle($orgSupplier, $supplierProduct, $this->validatedData);
    }


}
