<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 12 Nov 2024 09:44:58 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use App\Enums\Discounts\Offer\OfferStateEnum;
use App\Models\Discounts\Offer;
use App\Models\Discounts\OfferCampaign;
use Illuminate\Support\Arr;

trait WithStoreOffer
{
    protected function prepareOfferData(OfferCampaign|Offer $parent, $trigger, array $modelData): array
    {

        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'shop_id', $parent->shop_id);

        $status = false;
        if (Arr::get($modelData, 'state') == OfferStateEnum::ACTIVE) {
            $status = true;
        }

        data_set($modelData, 'status', $status);


        if ($trigger) {
            data_set($modelData, 'trigger_type', class_basename($trigger));
            data_set($modelData, 'trigger_id', $trigger->id);
        }

        return $modelData;
    }
}
