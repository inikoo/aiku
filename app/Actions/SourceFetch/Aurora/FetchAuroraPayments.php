<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 28 Feb 2023 14:04:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Accounting\Payment\StorePayment;
use App\Actions\Accounting\Payment\UpdatePayment;
use App\Models\Accounting\Payment;
use App\Services\Organisation\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraPayments extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:payments {organisations?*} {--s|source_id=} {--N|only_new : Fetch only new}  {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Payment
    {
        if ($paymentData = $organisationSource->fetchPayment($organisationSourceId)) {


            if ($payment = Payment::where('source_id', $paymentData['payment']['source_id'])->first()) {

                try {
                    $payment = UpdatePayment::make()->action(
                        payment: $payment,
                        modelData: $paymentData['payment'],
                        hydratorsDelay: $this->hydrateDelay
                    );
                    $this->markAuroraModel($payment);
                } catch (Exception $e) {
                    $this->recordError($organisationSource, $e, $paymentData['payment'], 'Payment', 'update');

                    return null;
                }

            } elseif ($paymentData['customer']) {

                try {
                    $payment = StorePayment::make()->action(
                        customer: $paymentData['customer'],
                        paymentAccount: $paymentData['paymentAccount'],
                        modelData: $paymentData['payment'],
                        hydratorsDelay: $this->hydrateDelay
                    );
                    $this->markAuroraModel($payment);
                } catch (Exception $e) {
                    //print_r($paymentData['payment']);
                    //dd($e);
                    $this->recordError($organisationSource, $e, $paymentData['payment'], 'Payment', 'store');

                    return null;
                }

            }


            return $payment;
        }

        return null;
    }

    public function markAuroraModel(Payment $payment): void
    {
        $sourceData = explode(':', $payment->source_id);
        DB::connection('aurora')->table('Payment Dimension')
            ->where('Payment Key', $sourceData[1])
            ->update(['aiku_id' => $payment->id]);
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Payment Dimension')
            ->select('Payment Key as source_id');
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query->orderBy('Payment Created Date');
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Payment Dimension');
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query->count();
    }
}
