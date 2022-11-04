<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:32:43 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


/** @noinspection PhpUnused */


namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Sales\Customer\StoreCustomer;
use App\Actions\Sales\Customer\UpdateCustomer;
use App\Models\Sales\Customer;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchCustomers extends FetchAction
{

    public string $commandSignature = 'fetch:customers {tenants?*} {--s|source_id=}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Customer
    {
        if ($customerData = $tenantSource->fetchCustomer($tenantSourceId)) {
            if ($customer = Customer::withTrashed()->where('source_id', $customerData['customer']['source_id'])
                ->first()) {
                $customer = UpdateCustomer::run($customer, $customerData['customer']);
            } else {
                $customer = StoreCustomer::run($customerData['shop'], $customerData['customer'], $customerData['addresses']);
            }

            DB::connection('aurora')->table('Customer Dimension')
                ->where('Customer Key', $customer->source_id)
                ->update(['aiku_id' => $customer->id]);

            return $customer;
        }

        return null;
    }

    function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Customer Dimension')
            ->select('Customer Key as source_id')
            ->orderBy('source_id');
    }

    function count(): ?int
    {
        return DB::connection('aurora')->table('Customer Dimension')->count();
    }

}
