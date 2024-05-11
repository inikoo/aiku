<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ShippingZoneSchema;

use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Catalogue\ShippingZoneSchemaResource;
use App\Models\Catalogue\ShippingZone;
use App\Models\Catalogue\ShippingZoneSchema;
use Lorisleiva\Actions\ActionRequest;

class UpdateShippingZoneSchema
{
    use WithActionUpdate;

    public function handle(ShippingZoneSchema $shippingZoneSchema, array $modelData): ShippingZoneSchema
    {
        return $this->update($shippingZoneSchema, $modelData);
    }

    //    public function authorize(ActionRequest $request): bool
    //    {
    //        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    //    }

    public function rules(): array
    {
        return [
            'name'   => ['required', 'max:250', 'string'],
            'status' => ['sometimes', 'required', 'boolean'],
            'data'   => ['sometimes', 'required']
        ];
    }

    public function action(ShippingZoneSchema $shippingZoneSchema, array $modelData): ShippingZoneSchema
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($shippingZoneSchema, $validatedData);
    }


    public function asController(ShippingZoneSchema $shippingZoneSchema, ActionRequest $request): ShippingZoneSchema
    {
        $request->validate();

        return $this->handle($shippingZoneSchema, $request->all());
    }


    public function jsonResponse(ShippingZone $shippingZone): ShippingZoneSchemaResource
    {
        return new ShippingZoneSchemaResource($shippingZone);
    }
}
