<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 12 Jan 2024 03:34:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\CRM\Customer\UpdateCustomer;
use App\Models\CRM\Customer;
use App\Services\Organisation\SourceOrganisationService;
use Arr;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchDeletedCustomers extends FetchAction
{
    public string $commandSignature = 'fetch:deleted-customers {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    /**
     * @throws \Throwable
     */
    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Customer
    {
        if ($customerData = $organisationSource->fetchDeletedCustomer($organisationSourceId)) {
            if ($customerData['customer']) {
                if ($customer = Customer::withTrashed()->where('source_id', $customerData['customer']['source_id'])
                    ->first()) {
                    if (Arr::get($customer->data, 'deleted.source') == 'aurora') {
                        $customer = UpdateCustomer::make()->action($customer, $customerData['customer'], 60, false);
                    }
                } else {

                    $customer = StoreCustomer::make()->action(
                        shop: $customerData['shop'],
                        modelData: $customerData['customer'],
                        hydratorsDelay: $this->hydrateDelay,
                        strict: false
                    );

                }

                $sourceData=explode(':', $customer->source_id);
                DB::connection('aurora')->table('Customer Deleted Dimension')
                    ->where('Customer Key', $sourceData[1])
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
