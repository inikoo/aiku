<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:32:43 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


/** @noinspection PhpUnused */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\CRM\Customer\UpdateCustomer;
use App\Models\CRM\Customer;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchCustomers extends FetchAction
{
    public string $commandSignature = 'fetch:customers {organisations?*} {--s|source_id=} {--S|shop= : Shop slug} {--w|with=* : Accepted values: clients orders web-users} {--N|only_new : Fetch only new}  {--d|db_suffix=} {--r|reset}';


    /**
     * @throws \Throwable
     */
    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Customer
    {
        $with = $this->with;

        if ($customerData = $organisationSource->fetchCustomer($organisationSourceId)) {
            if ($customer = Customer::withTrashed()->where('source_id', $customerData['customer']['source_id'])
                ->first()) {
                // print_r($customerData['customer']);
                $customer = UpdateCustomer::make()->asFetch(
                    $customer,
                    $customerData['customer']
                );

            } else {


                //print_r($customerData['customer']);
                $customer = StoreCustomer::make()->asFetch(
                    shop: $customerData['shop'],
                    modelData: $customerData['customer'],
                    hydratorsDelay: $this->hydrateDelay
                );

            }

            $sourceData = explode(':', $customer->source_id);

            if (in_array('products', $with)) {
                foreach (
                    DB::connection('aurora')
                        ->table('Product Dimension')
                        ->where('Product Customer Key', $sourceData[1])
                        ->select('Product ID as source_id')
                        ->orderBy('source_id')->get() as $order
                ) {
                    FetchProducts::run($organisationSource, $order->source_id);
                }
            }

            if ($customer->shop->type == 'dropshipping' and in_array('clients', $with)) {
                foreach (
                    DB::connection('aurora')
                        ->table('Customer Client Dimension')
                        ->where('Customer Client Customer Key', $sourceData[1])
                        ->select('Customer Client Key as source_id')
                        ->orderBy('source_id')->get() as $customerClient
                ) {
                    FetchCustomerClients::run($organisationSource, $customerClient->source_id);
                }
            }

            if (in_array('orders', $with)) {
                foreach (
                    DB::connection('aurora')
                        ->table('Order Dimension')
                        ->where('Order Customer Key', $sourceData[1])
                        ->select('Order Key as source_id')
                        ->orderBy('source_id')->get() as $order
                ) {
                    FetchOrders::run($organisationSource, $order->source_id);
                }
            }


            if (in_array('web-users', $with)) {
                foreach (
                    DB::connection('aurora')
                        ->table('Website User Dimension')
                        ->where('Website User Customer Key', $sourceData[1])
                        ->select('Website User Key as source_id')
                        ->orderBy('source_id')->get() as $order
                ) {
                    FetchWebUsers::run($organisationSource, $order->source_id);
                }
            }



            DB::connection('aurora')->table('Customer Dimension')
                ->where('Customer Key', $sourceData[1])
                ->update(['aiku_id' => $customer->id]);

            return $customer;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Customer Dimension')
            ->select('Customer Key as source_id')
            ->orderBy('source_id');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        if ($this->shop) {
            $sourceData=explode(':', $this->shop->source_id);
            $query->where('Customer Store Key', $sourceData[1]);
        }

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Customer Dimension');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }
        if ($this->shop) {
            $sourceData=explode(':', $this->shop->source_id);
            $query->where('Customer Store Key', $sourceData[1]);
        }

        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Customer Dimension')->update(['aiku_id' => null]);
    }
}
