<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jun 2024 15:40:49 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Dropshipping\Portfolio\StorePortfolio;
use App\Actions\Dropshipping\Portfolio\UpdatePortfolio;
use App\Models\Dropshipping\Portfolio;
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraPortfolios extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:portfolios {organisations?*} {--s|source_id=} {--d|db_suffix=} {--S|shop= : Shop slug} {--N|only_new : Fetch only new} ';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Portfolio
    {
        if ($portfolioData = $organisationSource->fetchPortfolio($organisationSourceId)) {

            if ($dropshippingCustomerPortfolio = Portfolio::where('source_id', $portfolioData['portfolio']['source_id'])->first()) {
                $dropshippingCustomerPortfolio = UpdatePortfolio::make()->action(
                    dropshippingCustomerPortfolio: $dropshippingCustomerPortfolio,
                    modelData: $portfolioData['portfolio']
                );
            } else {
                $dropshippingCustomerPortfolio = StorePortfolio::make()->action(
                    customer: $portfolioData['customer'],
                    modelData: $portfolioData['portfolio'],
                );
            }
            $sourceData = explode(':', $dropshippingCustomerPortfolio->source_id);


            DB::connection('aurora')->table('Customer Portfolio Fact')
                ->where('Customer Portfolio Key', $sourceData[1])
                ->update(['aiku_id' => $dropshippingCustomerPortfolio->id]);

            return $dropshippingCustomerPortfolio;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        $query= DB::connection('aurora')
            ->table('Customer Portfolio Fact')
            ->select('Customer Portfolio Key as source_id')
            ->orderBy('source_id');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        if ($this->shop) {
            $sourceData = explode(':', $this->shop->source_id);
            $query->where('Customer Portfolio Store Key', $sourceData[1]);
        }

        return $query;
    }

    public function count(): ?int
    {
        $query= DB::connection('aurora')->table('Customer Portfolio Fact');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        if ($this->shop) {
            $sourceData = explode(':', $this->shop->source_id);
            $query->where('Customer Portfolio Store Key', $sourceData[1]);
        }
        return $query->count();
    }


}
