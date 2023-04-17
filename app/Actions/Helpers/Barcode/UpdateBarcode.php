<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Barcode;

use App\Actions\WithActionUpdate;
use App\Http\Resources\Helpers\BarcodeResource;
use App\Models\Helpers\Barcode;
use Lorisleiva\Actions\ActionRequest;

class UpdateBarcode
{
    use WithActionUpdate;

    public function handle(Barcode $barcode, array $modelData): Barcode
    {
        return $this->update($barcode, $modelData, ['data']);
    }

//    public function authorize(ActionRequest $request): bool
//    {
//        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
//    }

    public function rules(): array
    {
        return [
            'slug' => ['sometimes', 'required'],
            'type' => ['sometimes', 'required'],
            'status' => ['sometimes', 'required'],
            'number' => ['sometimes', 'required'],
            'assigned_at' => ['sometimes', 'required'],
            'data' => ['sometimes', 'required'],
        ];
    }


    public function asController(Barcode $barcode, ActionRequest $request): Barcode
    {
        $request->validate();
        return $this->handle($barcode, $request->all());
    }


    public function jsonResponse(Barcode $barcode): BarcodeResource
    {
        return new BarcodeResource($barcode);
    }
}
