<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 19:41:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;


use App\Actions\Accounting\PaymentAccount\StorePaymentAccount;
use App\Actions\Accounting\PaymentAccount\UpdatePaymentAccount;
use App\Models\Accounting\PaymentAccount;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchPaymentAccount extends FetchAction
{


    public string $commandSignature = 'fetch:payment-accounts {tenants?*} {--s|source_id=}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?PaymentAccount
    {
        if ($paymentAccountData = $tenantSource->fetchPaymentAccount($tenantSourceId)) {
            if ($paymentAccount = PaymentAccount::where('source_id', $paymentAccountData['paymentAccount']['source_id'])
                ->first()) {
                $paymentAccount = UpdatePaymentAccount::run(
                    paymentAccount: $paymentAccount,
                    modelData:      $paymentAccountData['paymentAccount']
                );
            } else {
                $paymentAccount = StorePaymentAccount::run(
                    paymentServiceProvider: $paymentAccountData['paymentServiceProvider'],
                    modelData: $paymentAccountData['paymentAccount']
                );
            }

            DB::connection('aurora')->table('Payment Account Dimension')
                ->where('Payment Account Key', $paymentAccount->source_id)
                ->update(['aiku_id' => $paymentAccount->id]);

            return $paymentAccount;
        }

        return null;
    }


    function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Payment Account Dimension')
            ->select('Payment Account Key as source_id')
            ->orderBy('source_id');
    }

    function count(): ?int
    {
        return DB::connection('aurora')->table('Payment Account Dimension')->count();
    }

}
