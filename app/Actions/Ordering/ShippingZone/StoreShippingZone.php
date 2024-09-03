<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 15:08:02 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\ShippingZone;

use App\Actions\OrgAction;
use App\Models\Ordering\ShippingZone;
use App\Models\Ordering\ShippingZoneSchema;
use App\Rules\IUnique;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreShippingZone extends OrgAction
{
    use AsAction;
    use WithAttributes;

    public function handle(ShippingZoneSchema $shippingZoneSchema, array $modelData): ShippingZone
    {
        data_set($modelData, 'group_id', $shippingZoneSchema->group_id);
        data_set($modelData, 'organisation_id', $shippingZoneSchema->organisation_id);
        data_set($modelData, 'shop_id', $shippingZoneSchema->shop_id);

        /** @var $shippingZone ShippingZone */
        $shippingZone = $shippingZoneSchema->shippingZone()->create($modelData);
        $shippingZone->stats()->create();

        return $shippingZone;
    }

    public function rules(): array
    {
        $rules = [
            'code'        => [
                'required',
                new IUnique(
                    table: 'shipping_zones',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ]
                ),
                'between:2,16',
                'alpha_dash'
            ],
            'name'        => ['required', 'max:255', 'string'],
            'status'      => ['required', 'boolean'],
            'price'       => ['required', 'array'],
            'territories' => ['sometimes', 'array'],
            'position'    => ['required', 'integer'],
            'is_failover' => ['sometimes', 'boolean'],

        ];

        if (!$this->strict) {
            $rules['fetched_at'] = ['sometimes', 'date'];
            $rules['created_at'] = ['sometimes', 'date'];
        }

        return $rules;
    }

    public function action(ShippingZoneSchema $shippingZoneSchema, array $modelData, bool $strict = true): ShippingZone
    {
        $this->strict   = $strict;
        $this->asAction = true;
        $this->initialisationFromShop($shippingZoneSchema->shop, $modelData);

        return $this->handle($shippingZoneSchema, $this->validatedData);
    }
}
