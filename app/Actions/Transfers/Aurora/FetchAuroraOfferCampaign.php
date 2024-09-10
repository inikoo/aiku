<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 10 Sept 2024 13:56:02 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Discounts\OfferCampaign\StoreOfferCampaign;
use App\Actions\Discounts\OfferCampaign\UpdateOfferCampaign;
use App\Models\Discounts\OfferCampaign;
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraOfferCampaign extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:offer-campaigns {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?OfferCampaign
    {
        if ($offerCampaignData = $organisationSource->fetchOfferCampaign($organisationSourceId)) {

            if ($offerCampaign = OfferCampaign::where('source_id', $offerCampaignData['offer-campaign']['source_id'])->first()) {
                $offerCampaign = UpdateOfferCampaign::make()->action(
                    offerCampaign: $offerCampaign,
                    modelData: $offerCampaignData['offer-campaign']
                );
            } else {
                $offerCampaign = StoreOfferCampaign::make()->action(
                    shop: $offerCampaignData['workplace'],
                    modelData: $offerCampaignData['offer-campaign'],
                );


            }

            return $offerCampaign;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Deal Campaign Dimension')
            ->select('Deal Campaign Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Deal Campaign Dimension')->count();
    }


}
