<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 14:24:58 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Accounting\OrgPaymentServiceProvider\StoreOrgPaymentServiceProvider;
use App\Actions\Accounting\OrgPaymentServiceProvider\UpdateOrgPaymentServiceProvider;
use App\Models\Accounting\OrgPaymentServiceProvider;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchPaymentServiceProviders extends FetchAction
{
    public string $commandSignature = 'fetch:payment-service-providers {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?OrgPaymentServiceProvider
    {
        if ($paymentServiceProviderData = $organisationSource->fetchPaymentServiceProvider($organisationSourceId)) {
            if ($orgPaymentServiceProvider = OrgPaymentServiceProvider::where('source_id', $paymentServiceProviderData['orgPaymentServiceProvider']['source_id'])
                ->first()) {
                $orgPaymentServiceProvider = UpdateOrgPaymentServiceProvider::make()->action(
                    orgPaymentServiceProvider: $orgPaymentServiceProvider,
                    modelData:              $paymentServiceProviderData['orgPaymentServiceProvider']
                );
            } else {
                $orgPaymentServiceProvider = StoreOrgPaymentServiceProvider::make()->action(
                    paymentServiceProvider: $paymentServiceProviderData['paymentServiceProvider'],
                    organisation: $organisationSource->getOrganisation(),
                    modelData: $paymentServiceProviderData['orgPaymentServiceProvider'],
                );
            }

            $sourceId=explode(':', $orgPaymentServiceProvider->source_id);

            DB::connection('aurora')->table('Payment Service Provider Dimension')
                ->where('Payment Service Provider Key', $sourceId[1])
                ->update(['aiku_id' => $orgPaymentServiceProvider->id]);

            return $orgPaymentServiceProvider;
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
