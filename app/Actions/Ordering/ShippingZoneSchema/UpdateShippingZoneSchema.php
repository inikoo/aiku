<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\ShippingZoneSchema;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Ordering\ShippingZoneSchema\ShippingZoneSchemaTypeEnum;
use App\Models\Ordering\ShippingZoneSchema;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateShippingZoneSchema extends OrgAction
{
    use WithActionUpdate;


    private ShippingZoneSchema $shippingZoneSchema;

    public function handle(ShippingZoneSchema $shippingZoneSchema, array $modelData): ShippingZoneSchema
    {
        return $this->update($shippingZoneSchema, $modelData);
    }


    public function rules(): array
    {
        return [
            'name'                     => ['sometimes', 'max:255', 'string'],
            'type'                     => ['sometimes', Rule::enum(ShippingZoneSchemaTypeEnum::class)],
            'last_fetched_at'          => ['sometimes', 'date'],
        ];
    }

    public function action(ShippingZoneSchema $shippingZoneSchema, array $modelData, bool $audit=true): ShippingZoneSchema
    {
        if (!$audit) {
            ShippingZoneSchema::disableAuditing();
        }
        $this->shippingZoneSchema = $shippingZoneSchema;
        $this->initialisationFromShop($shippingZoneSchema->shop, $modelData);
        return $this->handle($shippingZoneSchema, $this->validatedData);
    }


    public function asController(ShippingZoneSchema $shippingZoneSchema, ActionRequest $request): ShippingZoneSchema
    {
        $this->shippingZoneSchema = $shippingZoneSchema;
        $this->initialisationFromShop($shippingZoneSchema->shop, $request);
        return $this->handle($shippingZoneSchema, $this->validatedData);
    }



}
