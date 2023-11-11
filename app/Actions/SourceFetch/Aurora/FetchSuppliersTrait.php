<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 10:26:55 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Helpers\GroupAddress\StoreGroupAddressAttachToModel;
use App\Actions\Helpers\GroupAddress\UpdateGroupAddress;
use App\Actions\Procurement\Marketplace\Supplier\StoreMarketplaceSupplier;
use App\Actions\Procurement\Supplier\UpdateSupplier;
use App\Actions\Organisation\Organisation\AttachSupplier;
use App\Enums\Procurement\SupplierOrganisation\SupplierOrganisationStatusEnum;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierOrganisation;
use App\Services\Organisation\SourceOrganisationService;

trait FetchSuppliersTrait
{
    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Supplier
    {
        $supplier     = null;
        $supplierData = $this->fetch($organisationSource, $organisationSourceId);
        if (!$supplierData) {
            return null;
        }

        if ($supplierData['supplier']['agent_id']) {
            if ($supplierData['owner']->id == app('currentTenant')->id) {
                $supplier = $this->processAgentSupplier($supplierData);
            }
        } else {
            $supplier = $this->processIndependentSupplier($supplierData);
        }

        if ($supplier) {
            if(array_key_exists('photo', $supplierData)) {
                foreach ($supplierData['photo'] as $photoData) {
                    $this->saveGroupImage($supplier, $photoData);
                }
            }
        }

        return $supplier;
    }
    public function processIndependentSupplier($supplierData): Supplier
    {
        $organisation = app('currentTenant');


        $supplier = Supplier::withTrashed()->where('code', $supplierData['supplier']['code'])->whereNull('agent_id')->first();
        if ($supplier) {
            if ($supplier->owner_id == $organisation->id) {
                $supplier = UpdateSupplier::run($supplier, $supplierData['supplier']);
                UpdateGroupAddress::run($supplier->getAddress('contact'), $supplierData['address']);
                $supplier->location = $supplier->getLocation();
                $supplier->save();
            } elseif (SupplierOrganisation::where('source_id', $supplierData['supplier']['source_id'])
                    ->where('tenant_id', $organisation->id)
                    ->count() == 0) {
                AttachSupplier::run(
                    $organisation,
                    $supplier,
                    [
                        'source_id' => $supplierData['supplier']['source_id'],
                        'status'    => SupplierOrganisationStatusEnum::ADOPTED
                    ]
                );
            }
        } else {
            $supplierData['supplier']['source_type'] = $organisation->slug;
            $supplier                                = StoreMarketplaceSupplier::run(
                owner: $organisation,
                agent: null,
                modelData: $supplierData['supplier'],
                addressData: $supplierData['address']
            );

            $organisation->suppliers()->updateExistingPivot($supplier, ['source_id' => $supplierData['supplier']['source_id']]);
        }


        return $supplier;
    }

    public function processAgentSupplier($supplierData): Supplier
    {
        $organisation = app('currentTenant');
        $agent        = $supplierData['agent'];

        $supplier = Supplier::withTrashed()->where('code', $supplierData['supplier']['code'])->where('agent_id', $agent->id)->first();

        if ($supplier) {
            if ($supplier->owner_id == $organisation->id) {

                $supplier = UpdateSupplier::run($supplier, $supplierData['supplier']);
                if ($supplier->getAddress('contact')) {
                    UpdateGroupAddress::run($supplier->getAddress('contact'), $supplierData['address']);
                } else {
                    StoreGroupAddressAttachToModel::run($supplier, $supplierData['address'], ['scope' => 'contact']);
                }
                $supplier->location = $supplier->getLocation();
                $supplier->save();
            } elseif (SupplierOrganisation::where('source_id', $supplierData['supplier']['source_id'])
                    ->where('tenant_id', $organisation->id)
                    ->count() == 0) {
                AttachSupplier::run(
                    $organisation,
                    $supplier,
                    [
                        'agent_id'  => $agent->id,
                        'source_id' => $supplierData['supplier']['source_id'],
                    ]
                );
            }
        } else {
            $supplier = StoreMarketplaceSupplier::run(
                owner: $agent->owner,
                agent: $agent,
                modelData: $supplierData['supplier'],
                addressData: $supplierData['address']
            );
            $organisation->suppliers()->updateExistingPivot($supplier, ['source_id' => $supplierData['supplier']['source_id']]);
        }



        return $supplier;
    }


}
