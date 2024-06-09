<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 12 Jan 2024 03:34:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\CRM\Customer\UpdateCustomer;
use App\Models\CRM\Customer;
use App\Transfers\SourceOrganisationService;
use Arr;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraDeletedCustomers extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:deleted-customers {organisations?*} {--s|source_id=} {--d|db_suffix=} {--N|only_new : Fetch only new} {--r|reset}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Customer
    {
        if ($customerData = $organisationSource->fetchDeletedCustomer($organisationSourceId)) {
            if ($customerData['customer']) {
                if ($customer = Customer::withTrashed()->where('source_id', $customerData['customer']['source_id'])
                    ->first()) {
                    if (Arr::get($customer->data, 'deleted.source') == 'aurora') {
                        try {
                            $customer = UpdateCustomer::make()->action($customer, $customerData['customer'], 60, false);
                            $this->recordChange($organisationSource, $customer->wasChanged());
                        } catch (Exception $e) {
                            $this->recordError($organisationSource, $e, $customerData['customer'], 'DeletedCustomer', 'update');

                            return null;
                        }
                    }
                } else {
                    try {
                        $customer = StoreCustomer::make()->action(
                            shop: $customerData['shop'],
                            modelData: $customerData['customer'],
                            hydratorsDelay: $this->hydrateDelay,
                            strict: false
                        );

                        $this->recordNew($organisationSource);
                    } catch (Exception $e) {
                        $this->recordError($organisationSource, $e, $customerData['customer'], 'DeletedCustomer', 'store');

                        return null;
                    }
                }

                $sourceData = explode(':', $customer->source_id);
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
        $query = DB::connection('aurora')
            ->table('Customer Deleted Dimension')
            ->select('Customer Key as source_id')
            ->orderBy('source_id');


        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }


        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Customer Deleted Dimension');
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Customer Deleted Dimension')->update(['aiku_id' => null]);
    }
}
