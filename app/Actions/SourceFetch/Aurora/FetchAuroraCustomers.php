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
use App\Actions\Market\RentalAgreement\StoreRentalAgreement;
use App\Enums\Market\RentalAgreement\RentalAgreementBillingCycleEnum;
use App\Enums\Market\RentalAgreement\RentalAgreementStateEnum;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Models\CRM\Customer;
use App\Services\Organisation\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraCustomers extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:customers {organisations?*} {--s|source_id=} {--S|shop= : Shop slug} {--w|with=* : Accepted values: clients orders web-users} {--N|only_new : Fetch only new} {--d|db_suffix=} {--r|reset}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Customer
    {
        $with = $this->with;

        if ($customerData = $organisationSource->fetchCustomer($organisationSourceId)) {
            if ($customer = Customer::withTrashed()->where('source_id', $customerData['customer']['source_id'])
                ->first()) {
                try {
                    $customer = UpdateCustomer::make()->action(
                        customer: $customer,
                        modelData: $customerData['customer'],
                        hydratorsDelay: 60,
                        strict: false
                    );
                    $this->recordChange($organisationSource, $customer->wasChanged());
                } catch (Exception $e) {
                    $this->recordError($organisationSource, $e, $customerData['customer'], 'Customer', 'update');

                    return null;
                }
            } else {
                try {
                    $customer = StoreCustomer::make()->action(
                        shop: $customerData['shop'],
                        modelData: $customerData['customer'],
                        hydratorsDelay: $this->hydrateDelay,
                        strict: false
                    );



                    if($customerData['shop']->type == ShopTypeEnum::FULFILMENT) {

                        $sourceData = explode(':', $customer->source_id);

                        $palletsCount= DB::connection('aurora')
                            ->table('Fulfilment Asset Dimension')
                            ->where('Fulfilment Asset Customer Key', $sourceData[1])->count();



                        if($palletsCount>0) {


                            StoreRentalAgreement::make()->action(
                                $customer->fulfilmentCustomer,
                                [
                                    'billing_cycle' => RentalAgreementBillingCycleEnum::MONTHLY,
                                    'state'         => RentalAgreementStateEnum::ACTIVE,
                                    'created_at'    => $customer->created_at,
                                ]
                            );


                        }


                    }


                    $this->recordNew($organisationSource);
                } catch (Exception $e) {
                    $this->recordError($organisationSource, $e, $customerData['customer'], 'Customer', 'store');

                    return null;
                }
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
                    FetchAuroraProducts::run($organisationSource, $order->source_id);
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
                    FetchAuroraCustomerClients::run($organisationSource, $customerClient->source_id);
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
                    FetchAuroraOrders::run($organisationSource, $order->source_id);
                }
            }



            if (in_array('web-users', $with)) {
                foreach (
                    DB::connection('aurora')
                        ->table('Website User Dimension')
                        ->where('Website User Customer Key', $sourceData[1])
                        ->select('Website User Key as source_id')
                        ->orderBy('source_id')->get() as $webUserData
                ) {

                    FetchAuroraWebUsers::run($organisationSource, $webUserData->source_id);
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
            $sourceData = explode(':', $this->shop->source_id);
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
            $sourceData = explode(':', $this->shop->source_id);
            $query->where('Customer Store Key', $sourceData[1]);
        }

        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Customer Dimension')->update(['aiku_id' => null]);
    }
}
