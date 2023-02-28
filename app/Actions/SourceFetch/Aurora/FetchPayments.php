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
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchPayments extends FetchAction
{

    public string $commandSignature = 'fetch:payments {tenants?*} {--s|source_id=}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Payment
    {
        if ($paymentData = $tenantSource->fetchPayment($tenantSourceId)) {
            if ($payment = Payment::where('source_id', $paymentData['payment']['source_id'])
                ->first()) {
                $payment = UpdatePayment::run(
                    payment:   $payment,
                    modelData: $paymentData['payment']
                );
                $this->markAuroraModel($payment);
            } else {

                if($paymentData['customer']){
                    $payment = StorePayment::run(
                        parent: $paymentData['customer'],
                        modelData:      $paymentData['payment']
                    );

                    $this->markAuroraModel($payment);
                }


            }


            return $payment;
        }

        return null;
    }

    function markAuroraModel(Payment $payment){
        DB::connection('aurora')->table('Payment Dimension')
            ->where('Payment Key', $payment->source_id)
            ->update(['aiku_id' => $payment->id]);

    }

    function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Payment Dimension')
            ->select('Payment Key as source_id')
            ->orderBy('source_id');
    }

    function count(): ?int
    {
        return DB::connection('aurora')->table('Payment Dimension')->count();
    }

}
