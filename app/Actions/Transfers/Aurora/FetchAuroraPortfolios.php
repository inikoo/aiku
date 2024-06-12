<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jun 2024 15:40:49 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Dropshipping\DropshippingCustomerPortfolio\StoreDropshippingCustomerPortfolio;
use App\Actions\Dropshipping\DropshippingCustomerPortfolio\UpdateDropshippingCustomerPortfolio;
use App\Models\Dropshipping\DropshippingCustomerPortfolio;
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraPortfolios extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:portfolios {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?DropshippingCustomerPortfolio
    {
        if ($portfolioData = $organisationSource->fetchPortfolio($organisationSourceId)) {

            if ($dropshippingCustomerPortfolio = DropshippingCustomerPortfolio::where('source_id', $portfolioData['portfolio']['source_id'])->first()) {
                $dropshippingCustomerPortfolio = UpdateDropshippingCustomerPortfolio::make()->action(
                    dropshippingCustomerPortfolio: $dropshippingCustomerPortfolio,
                    modelData: $portfolioData['portfolio']
                );
            } else {
                $dropshippingCustomerPortfolio = StoreDropshippingCustomerPortfolio::make()->action(
                    customer: $portfolioData['customer'],
                    modelData: $portfolioData['portfolio'],
                );
            }

            return $dropshippingCustomerPortfolio;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Customer Portfolio Fact')
            ->select('Customer Portfolio Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Customer Portfolio Fact')->count();
    }


}
