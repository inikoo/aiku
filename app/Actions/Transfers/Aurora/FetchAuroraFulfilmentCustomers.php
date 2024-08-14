<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:32:43 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Fulfilment\RecurringBill\StoreRecurringBill;
use App\Actions\Fulfilment\RecurringBillTransaction\StoreRecurringBillTransaction;
use App\Actions\Fulfilment\RentalAgreement\StoreRentalAgreement;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementBillingCycleEnum;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementStateEnum;
use App\Models\CRM\Customer;
use App\Transfers\Aurora\WithAuroraAttachments;
use App\Transfers\Aurora\WithAuroraParsers;
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FetchAuroraFulfilmentCustomers extends FetchAuroraAction
{
    use WithAuroraAttachments;
    use WithAuroraParsers;

    public string $commandSignature = 'fetch:fulfilment-customers {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Customer
    {
        $customer   = $this->parseCustomer($organisationSource->getOrganisation()->id.':'.$organisationSourceId);
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
            if ($customer->fulfilmentCustomer->customer->webUsers()->count() == 0) {
                $rentalAgreementData['username'] = $customer->email ?? $customer->reference;
                $rentalAgreementData['email']    = $customer->email;
            }


            StoreRentalAgreement::make()->action(
                $customer->fulfilmentCustomer,
                $rentalAgreementData
            );
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


            $recurringBill = StoreRecurringBill::make()->action(
                $customer->fulfilmentCustomer->rentalAgreement,
                [
                    'start_date' => $startDate,
                ]
            );

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


                StoreRecurringBillTransaction::make()->action(
                    $recurringBill,
                    $pallet,
                    [
                        'start_date' => $palletStartDate,
                    ]
                );
            }
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
