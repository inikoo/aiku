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
use App\Actions\Tenancy\Tenant\AttachSupplier;
use App\Actions\Utils\StoreImage;
use App\Enums\Procurement\SupplierTenant\SupplierTenantStatusEnum;
use App\Models\Procurement\Supplier;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchSuppliers extends FetchAction
{
    public string $commandSignature = 'fetch:suppliers {tenants?*} {--s|source_id=} {--d|db_suffix=} {--N|only_new : Fetch only new}';


    public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Supplier
    {
        $supplier     = null;
        $supplierData = $tenantSource->fetchSupplier($tenantSourceId);
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

        if($supplier) {
            foreach ($supplierData['photo'] as $photoData) {
                $this->saveGroupImage($supplier, $photoData);
            }
        }

        return $supplier;
    }


    public function processIndependentSupplier($supplierData): Supplier
    {
        $tenant = app('currentTenant');

        if ($supplier = Supplier::withTrashed()->where('source_id', $supplierData['supplier']['source_id'])->where('source_type', $tenant->slug)->first()) {
            $supplier = UpdateSupplier::run($supplier, $supplierData['supplier']);
            UpdateGroupAddress::run($supplier->getAddress('contact'), $supplierData['address']);
            $supplier->location = $supplier->getLocation();
            $supplier->save();
        } else {
            $supplier = Supplier::withTrashed()->where('code', $supplierData['supplier']['code'])->first();
            if ($supplier) {
                AttachSupplier::run(
                    $tenant,
                    $supplier,
                    [
                        'source_id' => $supplierData['supplier']['source_id'],
                        'status'    => SupplierTenantStatusEnum::ADOPTED
                    ]
                );
            } else {
                $supplierData['supplier']['source_type'] = $tenant->slug;
                $supplier                                = StoreMarketplaceSupplier::run(
                    owner: $tenant,
                    agent: null,
                    modelData: $supplierData['supplier'],
                    addressData: $supplierData['address']
                );

                $tenant->suppliers()->updateExistingPivot($supplier, ['source_id' => $supplierData['supplier']['source_id']]);

                DB::connection('aurora')->table('Supplier Dimension')
                    ->where('Supplier Key', $supplier->source_id)
                    ->update(['aiku_id' => $supplier->id]);

            }
        }

        return $supplier;
    }

    public function processAgentSupplier($supplierData): Supplier
    {
        $tenant = app('currentTenant');

        if ($supplier = Supplier::withTrashed()->where('source_id', $supplierData['supplier']['source_id'])
            ->first()) {
            $supplier = UpdateSupplier::run($supplier, $supplierData['supplier']);

            if ($supplier->getAddress('contact')) {
                UpdateGroupAddress::run($supplier->getAddress('contact'), $supplierData['address']);
            } else {
                StoreGroupAddressAttachToModel::run($supplier, $supplierData['address'], ['scope' => 'contact']);
            }
            $supplier->location = $supplier->getLocation();
            $supplier->save();
        } else {
            $supplier = Supplier::withTrashed()->where('code', $supplierData['supplier']['code'])->first();



            if ($supplier) {
                AttachSupplier::run(
                    $tenant,
                    $supplier,
                    [
                        'agent_id'  => $supplierData['agent']->id,
                        'source_id' => $supplierData['supplier']['source_id'],
                    ]
                );
            } else {
                $supplier = StoreMarketplaceSupplier::run(
                    owner: $supplierData['owner'],
                    agent: $supplierData['agent'],
                    modelData: $supplierData['supplier'],
                    addressData: $supplierData['address']
                );
                $tenant->suppliers()->updateExistingPivot($supplier, ['source_id' => $supplierData['supplier']['source_id']]);

                DB::connection('aurora')->table('Supplier Dimension')
                    ->where('Supplier Key', $supplier->source_id)
                    ->update(['aiku_id' => $supplier->id]);

            }
        }


        foreach ($supplierData['photo'] ?? [] as $profileImage) {
            if (isset($profileImage['image_path']) and isset($profileImage['filename'])) {
                StoreImage::run($supplier, $profileImage['image_path'], $profileImage['filename']);
            }
        }

        return $supplier;
    }


    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Supplier Dimension')
            ->leftJoin('Agent Supplier Bridge', 'Agent Supplier Supplier Key', 'Supplier Key')
            ->select('Supplier Key as source_id')
            ->where('aiku_ignore', 'No')
            ->orderBy('source_id');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')
            ->table('Supplier Dimension')
            ->leftJoin('Agent Supplier Bridge', 'Agent Supplier Supplier Key', 'Supplier Key')
            ->where('aiku_ignore', 'No');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query->count();
    }
}
