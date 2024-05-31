<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Barcode;

use App\Actions\GrpAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Helpers\Barcode\BarcodeStatusEnum;
use App\Http\Resources\Helpers\BarcodeResource;
use App\Models\Helpers\Barcode;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateBarcode extends GrpAction
{
    use WithActionUpdate;

    private Barcode $barcode;

    public function handle(Barcode $barcode, array $modelData): Barcode
    {
        return $this->update($barcode, $modelData, ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("goods.edit");
    }

    public function rules(): array
    {
        return [
            'number' => ['sometimes', 'required', 'numeric'],
            'note'   => ['sometimes', 'nullable', 'string', 'max:1000'],
            'data'   => ['sometimes', 'nullable', 'array'],
            'status' => ['sometimes', 'required', Rule::enum(BarcodeStatusEnum::class)],


        ];
    }

    public function asController(Barcode $barcode, ActionRequest $request): Barcode
    {
        $this->initialisation(app('group'), $request);

        return $this->handle($barcode, $request->validated());
    }

    public function action(Barcode $barcode, array $modelData): Barcode
    {
        $this->asAction = true;
        $this->barcode  = $barcode;
        $this->initialisation($barcode->group, $modelData);

        return $this->handle($barcode, $this->validatedData);
    }

    public function jsonResponse(Barcode $barcode): BarcodeResource
    {
        return new BarcodeResource($barcode);
    }
}
