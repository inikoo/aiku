<?php


namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Sales\Customer\StoreCustomer;
use App\Actions\Sales\Customer\UpdateCustomer;
use App\Models\Sales\Customer;
use App\Services\Tenant\SourceTenantService;
use Arr;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchDeletedCustomers extends FetchAction
{

    public string $commandSignature = 'fetch:deleted-customers {tenants?*} {--s|source_id=}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Customer
    {
        if ($customerData = $tenantSource->fetchDeletedCustomer($tenantSourceId)) {
            if($customerData['customer']) {
                if ($customer = Customer::withTrashed()->where('source_id', $customerData['customer']['source_id'])
                    ->first()) {

                    if(Arr::get($customer->data,'deleted.source')=='aurora'){
                        $customer = UpdateCustomer::run($customer, $customerData['customer']);
                    }

                } else {
                    $customer = StoreCustomer::run($customerData['shop'], $customerData['customer'], $customerData['addresses']);
                }

                DB::connection('aurora')->table('Customer Deleted Dimension')
                    ->where('Customer Key', $customer->source_id)
                    ->update(['aiku_id' => $customer->id]);

                return $customer;
            }
        }

        return null;
    }

    function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Customer Deleted Dimension')
            ->select('Customer Key as source_id')
            ->orderBy('source_id');
    }

    function count(): ?int
    {
        return DB::connection('aurora')->table('Customer Deleted Dimension')->count();
    }

}
