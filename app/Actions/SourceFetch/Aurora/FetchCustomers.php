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
use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\Helpers\TaxNumber\DeleteTaxNumber;
use App\Actions\Helpers\TaxNumber\StoreTaxNumber;
use App\Actions\Helpers\TaxNumber\UpdateTaxNumber;
use App\Models\CRM\Customer;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FetchCustomers extends FetchAction
{
    public string $commandSignature = 'fetch:customers {tenants?*} {--s|source_id=} {--S|shop= : Shop slug} {--w|with=* : Accepted values: clients orders web-users} {--N|only_new : Fetch only new}  {--d|db_suffix=} {--r|reset}';


    /**
     * @throws \Throwable
     */
    public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Customer
    {
        $with = $this->with;

        if ($customerData = $tenantSource->fetchCustomer($tenantSourceId)) {
            if ($customer = Customer::withTrashed()->where('source_id', $customerData['customer']['source_id'])
                ->first()) {
                $customer = UpdateCustomer::run($customer, $customerData['customer']);

                UpdateAddress::run($customer->getAddress('contact'), $customerData['contact_address']);
                $customer->location = $customer->getLocation();
                $customer->save();

                $deliveryAddress = $customer->getAddress('delivery');

                if (!empty($customerData['delivery_address'])) {
                    if ($deliveryAddress) {
                        UpdateAddress::run($deliveryAddress, $customerData['delivery_address']);
                    } else {
                        StoreAddressAttachToModel::run($customer, $customerData['delivery_address'], ['scope' => 'delivery']);
                    }
                } elseif ($deliveryAddress) {
                    $customer->addresses()->detach($deliveryAddress->id);
                    $deliveryAddress->delete();
                }

                if ($customerData['tax_number']) {
                    if (!$customer->taxNumber) {
                        if (!Arr::get($customerData, 'tax_number.data.name')) {
                            Arr::forget($customerData, 'tax_number.data.name');
                        }

                        if (!Arr::get($customerData, 'tax_number.data.address')) {
                            Arr::forget($customerData, 'tax_number.data.address');
                        }
                        StoreTaxNumber::run(
                            owner: $customer,
                            modelData: $customerData['tax_number']
                        );
                    } else {
                        UpdateTaxNumber::run($customer->taxNumber, $customerData['tax_number']);
                    }
                } else {
                    if ($customer->taxNumber) {
                        DeleteTaxNumber::run($customer->taxNumber);
                    }
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


                if ($customerData['tax_number']) {
                    if (!Arr::get($customerData, 'tax_number.data.name')) {
                        Arr::forget($customerData, 'tax_number.data.name');
                    }

                    if (!Arr::get($customerData, 'tax_number.data.address')) {
                        Arr::forget($customerData, 'tax_number.data.address');
                    }

                    StoreTaxNumber::run(
                        owner: $customer,
                        modelData: $customerData['tax_number']
                    );
                }
            }

            if (in_array('products', $with)) {
                foreach (
                    DB::connection('aurora')
                        ->table('Product Dimension')
                        ->where('Product Customer Key', $customer->source_id)
                        ->select('Product ID as source_id')
                        ->orderBy('source_id')->get() as $order
                ) {
                    FetchProducts::run($tenantSource, $order->source_id);
                }
            }

            if ($customer->shop->subtype == 'dropshipping' and in_array('clients', $with)) {
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

            if (in_array('orders', $with)) {
                foreach (
                    DB::connection('aurora')
                        ->table('Order Dimension')
                        ->where('Order Customer Key', $customer->source_id)
                        ->select('Order Key as source_id')
                        ->orderBy('source_id')->get() as $order
                ) {
                    FetchOrders::run($tenantSource, $order->source_id);
                }
            }


            if (in_array('web-users', $with)) {
                foreach (
                    DB::connection('aurora')
                        ->table('Website User Dimension')
                        ->where('Website User Customer Key', $customer->source_id)
                        ->select('Website User Key as source_id')
                        ->orderBy('source_id')->get() as $order
                ) {
                    FetchWebUsers::run($tenantSource, $order->source_id);
                }
            }


            DB::connection('aurora')->table('Customer Dimension')
                ->where('Customer Key', $customer->source_id)
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
            $query->where('Customer Store Key', $this->shop->source_id);
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
            $query->where('Customer Store Key', $this->shop->source_id);
        }

        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Customer Dimension')->update(['aiku_id' => null]);
    }
}
