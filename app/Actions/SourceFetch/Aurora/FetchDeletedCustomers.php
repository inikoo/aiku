<?php

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\CRM\Customer\UpdateCustomer;
use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Models\CRM\Customer;
use App\Services\Tenant\SourceTenantService;
use Arr;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchDeletedCustomers extends FetchAction
{
    public string $commandSignature = 'fetch:deleted-customers {tenants?*} {--s|source_id=} {--d|db_suffix=}';


    /**
     * @throws \Throwable
     */
    public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Customer
    {
        if ($customerData = $tenantSource->fetchDeletedCustomer($tenantSourceId)) {
            if ($customerData['customer']) {
                if ($customer = Customer::withTrashed()->where('source_id', $customerData['customer']['source_id'])
                    ->first()) {
                    if (Arr::get($customer->data, 'deleted.source') == 'aurora') {
                        $customer = UpdateCustomer::run($customer, $customerData['customer']);
                    }
                } else {
                    $customer = StoreCustomer::make()->asFetch(
                        shop: $customerData['shop'],
                        customerData: $customerData['customer'],
                        customerAddressesData: $customerData['contact_address'],
                        hydratorsDelay: $this->hydrateDelay
                    );


                    if (!empty($customerData['delivery_address'])) {
                        StoreAddressAttachToModel::run($customer, $customerData['delivery_address'], ['scope' => 'delivery']);
                    }
                }

                DB::connection('aurora')->table('Customer Deleted Dimension')
                    ->where('Customer Key', $customer->source_id)
                    ->update(['aiku_id' => $customer->id]);

                return $customer;
            }
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Customer Deleted Dimension')
            ->select('Customer Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Customer Deleted Dimension')->count();
    }
}
