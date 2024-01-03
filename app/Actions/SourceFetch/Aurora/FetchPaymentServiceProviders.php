<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 14:24:58 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Accounting\PaymentServiceProvider\StorePaymentServiceProvider;
use App\Actions\Accounting\PaymentServiceProvider\UpdatePaymentServiceProvider;
use App\Models\Accounting\PaymentServiceProvider;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchPaymentServiceProviders extends FetchAction
{
    public string $commandSignature = 'fetch:payment-service-providers {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?PaymentServiceProvider
    {
        if ($paymentServiceProviderData = $organisationSource->fetchPaymentServiceProvider($organisationSourceId)) {
            if ($paymentServiceProvider = PaymentServiceProvider::where('source_id', $paymentServiceProviderData['paymentServiceProvider']['source_id'])
                ->first()) {
                $paymentServiceProvider = UpdatePaymentServiceProvider::make()->action(
                    paymentServiceProvider: $paymentServiceProvider,
                    modelData:              $paymentServiceProviderData['paymentServiceProvider']
                );
            } else {
                $paymentServiceProvider = StorePaymentServiceProvider::make()->action(
                    organisation: $organisationSource->getOrganisation(),
                    modelData: $paymentServiceProviderData['paymentServiceProvider']
                );
            }

            $sourceId=explode(':', $paymentServiceProvider->source_id);


            DB::connection('aurora')->table('Payment Service Provider Dimension')
                ->where('Payment Service Provider Key', $sourceId[1])
                ->update(['aiku_id' => $paymentServiceProvider->id]);

            return $paymentServiceProvider;
        }

        return null;
    }


    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Payment Service Provider Dimension')
            ->select('Payment Service Provider Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Payment Service Provider Dimension')->count();
    }
}
