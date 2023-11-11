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
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchPayments extends FetchAction
{
    public string $commandSignature = 'fetch:payments {tenants?*} {--s|source_id=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Payment
    {
        if ($paymentData = $organisationSource->fetchPayment($organisationSourceId)) {
            if ($payment = Payment::where('source_id', $paymentData['payment']['source_id'])
                ->first()) {
                $payment = UpdatePayment::run(
                    payment: $payment,
                    modelData: $paymentData['payment']
                );
                $this->markAuroraModel($payment);
            } else {
                if ($paymentData['customer']) {
                    $payment = StorePayment::run(
                        customer: $paymentData['customer'],
                        paymentAccount: $paymentData['paymentAccount'],
                        modelData: $paymentData['payment']
                    );

                    $this->markAuroraModel($payment);
                }
            }


            return $payment;
        }

        return null;
    }

    public function markAuroraModel(Payment $payment): void
    {
        DB::connection('aurora')->table('Payment Dimension')
            ->where('Payment Key', $payment->source_id)
            ->update(['aiku_id' => $payment->id]);
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Payment Dimension')
            ->select('Payment Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Payment Dimension')->count();
    }
}
