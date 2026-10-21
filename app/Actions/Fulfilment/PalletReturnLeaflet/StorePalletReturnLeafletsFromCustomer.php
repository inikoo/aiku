<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturnLeaflet;

use App\Enums\Catalogue\Leaflet\LeafletStateEnum;
use App\Enums\Dispatching\DeliveryNoteLeaflet\DeliveryNoteLeafletStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnTypeEnum;
use App\Models\Billables\ModelHasLeaflet;
use App\Models\Fulfilment\PalletReturn;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * Copies the customer's included inserts onto the return when it is submitted, so later changes
 * to the customer's inserts do not change what this return is packed with.
 */
class StorePalletReturnLeafletsFromCustomer
{
    use AsObject;

    public function handle(PalletReturn $palletReturn): void
    {
        if ($palletReturn->type === PalletReturnTypeEnum::PALLET || $palletReturn->leaflets()->exists()) {
            return;
        }

        $shop = $palletReturn->fulfilment->shop;

        ModelHasLeaflet::where('model_type', 'Customer')
            ->where('model_id', $palletReturn->fulfilmentCustomer->customer_id)
            ->where('shop_id', $shop->id)
            ->where('state', LeafletStateEnum::ACTIVE)
            ->whereNotNull('media_id')
            ->orderBy('id')
            ->get()
            ->each(fn (ModelHasLeaflet $customerLeaflet) => $palletReturn->leaflets()->create([
                'group_id'             => $palletReturn->group_id,
                'organisation_id'      => $palletReturn->organisation_id,
                'shop_id'              => $shop->id,
                'model_has_leaflet_id' => $customerLeaflet->id,
                'type'                 => $customerLeaflet->type,
                'name'                 => $customerLeaflet->name,
                'media_id'             => $customerLeaflet->media_id,
                'copies'               => 1,
                'state'                => DeliveryNoteLeafletStateEnum::PENDING_PRINT,
            ]));
    }
}
