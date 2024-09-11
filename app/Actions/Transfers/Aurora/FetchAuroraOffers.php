<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 11 Sept 2024 19:51:35 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Discounts\Offer\StoreOffer;
use App\Actions\Discounts\Offer\UpdateOffer;
use App\Models\Discounts\Offer;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraOffers extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:offers {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Offer
    {
        if ($offerData = $organisationSource->fetchOffer($organisationSourceId)) {

            if ($offer = Offer::withTrashed()->where('source_id', $offerData['offer']['source_id'])
                ->first()) {
                // try {
                $offer = UpdateOffer::make()->action(
                    offer: $offer,
                    modelData: $offerData['offer'],
                    strict: false,
                    audit: false
                );
                $this->recordChange($organisationSource, $offer->wasChanged());
                //                } catch (Exception $e) {
                //                    $this->recordError($organisationSource, $e, $offerData['offer'], 'Offer', 'update');
                //
                //                    return null;
                //                }
            } else {
                //  try {
                $offer = StoreOffer::make()->action(
                    offerCampaign: $offerData['offer_campaign'],
                    trigger: $offerData['trigger'],
                    modelData: $offerData['offer'],
                    strict: false,
                );
                //
                //                    $this->recordNew($organisationSource);
                //                } catch (Exception $e) {
                //                    $this->recordError($organisationSource, $e, $offerData['offer'], 'Offer', 'store');
                //
                //                    return null;
                //                }
            }


            return $offer;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Deal Dimension')
            ->select('Deal Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Deal Dimension')->count();
    }


}
