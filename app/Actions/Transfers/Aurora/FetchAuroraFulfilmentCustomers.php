<?php

/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:32:43 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Fulfilment\RecurringBill\CalculateRecurringBillRentalAmount;
use App\Actions\Fulfilment\RecurringBill\CalculateRecurringBillTotals;
use App\Actions\Fulfilment\RecurringBill\StoreRecurringBill;
use App\Actions\Fulfilment\RecurringBillTransaction\StoreRecurringBillTransaction;
use App\Actions\Fulfilment\RentalAgreement\StoreRentalAgreement;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementBillingCycleEnum;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementStateEnum;
use App\Models\CRM\Customer;
use App\Transfers\Aurora\WithAuroraAttachments;
use App\Transfers\Aurora\WithAuroraParsers;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class FetchAuroraFulfilmentCustomers extends FetchAuroraAction
{
    use WithAuroraAttachments;
    use WithAuroraParsers;

    public bool $saveRecurringBills = true;

    public string $commandSignature = 'fetch:fulfilment_customers {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Customer
    {
        $customer = $this->parseCustomer($organisationSource->getOrganisation()->id.':'.$organisationSourceId);
        if (!$customer) {
            return null;
        }

        $sourceData = explode(':', $customer->source_id);

        $palletsCount = DB::connection('aurora')
            ->table('Fulfilment Asset Dimension')
            ->where('Fulfilment Asset Customer Key', $sourceData[1])->count();


        if ($palletsCount > 0) {
            foreach (
                DB::connection('aurora')
                    ->table('Website User Dimension')
                    ->where('Website User Customer Key', $sourceData[1])
                    ->select('Website User Key as source_id')
                    ->orderBy('source_id')->get() as $webUserData
            ) {
                FetchAuroraWebUsers::run($organisationSource, $webUserData->source_id);
            }

            $rentalAgreementData = [
                'billing_cycle' => RentalAgreementBillingCycleEnum::MONTHLY,
                'state'         => RentalAgreementStateEnum::ACTIVE,
                'created_at'    => $customer->created_at,
            ];

            $customer->fulfilmentCustomer->refresh();
            if ($customer->fulfilmentCustomer->customer->webUsers()->count() == 0 and $customer->email) {
                $rentalAgreementData['username'] = $customer->email;
                $rentalAgreementData['email']    = $customer->email;
            }


            if (!$customer->fulfilmentCustomer->rentalAgreement) {
                try {
                    StoreRentalAgreement::make()->action(
                        $customer->fulfilmentCustomer,
                        $rentalAgreementData
                    );
                } catch (Exception|Throwable $e) {
                    print_r($e->getMessage());
                }
            }
        }

        $storingPalletsCount = DB::connection('aurora')
            ->table('Fulfilment Asset Dimension')
            ->where('Fulfilment Asset State', 'BookedIn')
            ->where('Fulfilment Asset Customer Key', $sourceData[1])
            ->count();

        if ($storingPalletsCount > 0) {
            $startDate = DB::connection('aurora')
                ->table('Fulfilment Asset Dimension')
                ->where('Fulfilment Asset State', 'BookedIn')
                ->where('Fulfilment Asset Customer Key', $sourceData[1])
                ->whereNotNull('Fulfilment Asset Last Rent Order Date')
                ->min('Fulfilment Asset Last Rent Order Date');

            if (!$startDate) {
                $startDate = DB::connection('aurora')
                    ->table('Fulfilment Asset Dimension')
                    ->where('Fulfilment Asset State', 'BookedIn')
                    ->where('Fulfilment Asset Customer Key', $sourceData[1])
                    ->whereNull('Fulfilment Asset Last Rent Order Date')
                    ->min('Fulfilment Asset From');
            }

            if (!$startDate) {
                $startDate = now();
            } else {
                $startDate = Carbon::parse($startDate);
            }

            $recurringBill = $customer->fulfilmentCustomer->currentRecurringBill;

            $customer->refresh();
            $rentalAgreement = $customer->fulfilmentCustomer->rentalAgreement;
            if (!$recurringBill) {

                $recurringBill = StoreRecurringBill::make()->action(
                    rentalAgreement: $rentalAgreement,
                    modelData:[
                        'start_date' => $startDate,
                    ],
                    hydratorsDelay: 120
                );
            }




            foreach (
                DB::connection('aurora')
                    ->table('Fulfilment Asset Dimension')
                    ->where('Fulfilment Asset State', 'BookedIn')
                    ->where('Fulfilment Asset Customer Key', $sourceData[1])
                    ->get() as $palletData
            ) {
                $pallet = $this->parsePallet($organisationSource->getOrganisation()->id.':'.$palletData->{'Fulfilment Asset Key'});

                if ($palletData->{'Fulfilment Asset Last Rent Order Date'}) {
                    $palletStartDate = Carbon::parse($palletData->{'Fulfilment Asset Last Rent Order Date'});
                } else {
                    $palletStartDate = Carbon::parse($palletData->{'Fulfilment Asset From'});
                }

                //print $palletStartDate->toDateTimeString()."\n";

                if ($this->saveRecurringBills) {
                    $recurringBillTransaction = $recurringBill->transactions()->where('item_type', 'Pallet')->where('item_id', $pallet->id)->first();

                    if (!$recurringBillTransaction) {
                        StoreRecurringBillTransaction::make()->action(
                            recurringBill: $recurringBill,
                            item:$pallet,
                            modelData:[
                                'start_date' => $palletStartDate,
                            ],
                            hydratorsDelay: 120
                        );
                    }
                }
            }

            CalculateRecurringBillRentalAmount::make()->action(
                recurringBill:$recurringBill,
                hydratorsDelay: 60
            );

            CalculateRecurringBillTotals::make()->action(
                recurringBill:$recurringBill,
                hydratorsDelay: 60
            );
        }

        return $customer;
    }


    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Customer Dimension');
        $query->leftJoin('Store Dimension', 'Customer Store Key', '=', 'Store Key');
        $query->where('Store Type', 'Fulfilment');
        $query->select('Customer Key as source_id')
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
        $query->leftJoin('Store Dimension', 'Customer Store Key', '=', 'Store Key');
        $query->where('Store Type', 'Fulfilment');
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }
        if ($this->shop) {
            $sourceData = explode(':', $this->shop->source_id);
            $query->where('Customer Store Key', $sourceData[1]);
        }

        return $query->count();
    }


}
