<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 15:08:02 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\ShippingZoneSchema;

use App\Enums\Ordering\ShippingZoneSchema\ShippingZoneSchemaTypeEnum;
use App\Models\Ordering\ShippingZoneSchema;
use App\Models\Catalogue\Shop;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreShippingZoneSchema
{
    use AsAction;
    use WithAttributes;

    public function handle(Shop $shop, array $modelData): ShippingZoneSchema
    {
        data_set($modelData, 'group_id', $shop->group_id);
        data_set($modelData, 'organisation_id', $shop->organisation_id);
        /** @var $shippingZoneSchema ShippingZoneSchema */
        $shippingZoneSchema= $shop->shippingZoneSchemas()->create($modelData);
        $shippingZoneSchema->stats()->create();

        return $shippingZoneSchema;

    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'max:255', 'string'],
            'type'        => ['sometimes', Rule::enum(ShippingZoneSchemaTypeEnum::class)],
            'fetched_at'  => ['sometimes', 'date'],
            'source_id'   => ['sometimes', 'nullable', 'string'],
            'created_at'  => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function action(Shop $shop, array $modelData): ShippingZoneSchema
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($shop, $validatedData);
    }
}
