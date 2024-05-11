<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ShippingZone;

use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Catalogue\ShippingZoneResource;
use App\Models\Catalogue\ShippingZone;
use Lorisleiva\Actions\ActionRequest;

class UpdateShippingZone
{
    use WithActionUpdate;

    public function handle(ShippingZone $shippingZone, array $modelData): ShippingZone
    {
        return $this->update($shippingZone, $modelData);
    }

    //    public function authorize(ActionRequest $request): bool
    //    {
    //        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    //    }


    public function rules(): array
    {
        return [
            'code'   => ['required', 'unique:shipping_zones', 'between:2,9', 'alpha'],
            'name'   => ['required', 'max:250', 'string'],
            'status' => ['sometimes', 'required', 'boolean']
        ];
    }

    public function action(ShippingZone $shippingZone, array $modelData): ShippingZone
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($shippingZone, $validatedData);
    }

    public function asController(ShippingZone $shippingZone, ActionRequest $request): ShippingZone
    {
        $request->validate();

        return $this->handle($shippingZone, $request->all());
    }


    public function jsonResponse(ShippingZone $shippingZone): ShippingZoneResource
    {
        return new ShippingZoneResource($shippingZone);
    }
}
