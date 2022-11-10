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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchCustomers extends FetchAction
{

    public string $commandSignature = 'fetch:customers {tenants?*} {--s|source_id=} {--S|shop= : Shop slug} {--w|with=* : Accepted values: clients}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Customer
    {
        if ($customerData = $tenantSource->fetchCustomer($tenantSourceId)) {
            if ($customer = Customer::withTrashed()->where('source_id', $customerData['customer']['source_id'])
                ->first()) {
                $customer = UpdateCustomer::run($customer, $customerData['customer']);
            } else {
                $customer = StoreCustomer::run($customerData['shop'], $customerData['customer'], $customerData['addresses']);
            }

            if ($customer->shop->type == 'fulfilment_house' and in_array('clients', $this->with)) {
                foreach (
                    DB::connection('aurora')
                        ->table('Customer Client Dimension')
                        ->where('Customer Client Customer Key', $customer->source_id)
                        ->select('Customer Client Key as source_id')
                        ->orderBy('source_id')->get() as $customerClient
                ) {
                    FetchCustomerClients::run($tenantSource, $customerClient->source_id);
                }
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
        $query = DB::connection('aurora')
            ->table('Customer Dimension')
            ->select('Customer Key as source_id')
            ->orderBy('source_id');

        if ($this->shop) {
            $query->where('Customer Store Key', $this->shop->source_id);
        }

        return $query;
    }

    function count(): ?int
    {
        $query = DB::connection('aurora')->table('Customer Dimension');
        if ($this->shop) {
            $query->where('Customer Store Key', $this->shop->source_id);
        }

        return $query->count();
    }

}
