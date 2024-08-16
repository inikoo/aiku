<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:33 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Barcode;

use App\Actions\GrpAction;
use App\Enums\Helpers\Barcode\BarcodeStatusEnum;
use App\Enums\Helpers\Barcode\BarcodeTypeEnum;
use App\Models\Helpers\Barcode;
use App\Models\SysAdmin\Group;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreBarcode extends GrpAction
{
    public function handle(Group $group, $modelData): Barcode
    {
        /** @var Barcode $barcode */
        $barcode = $group->barcodes()->create($modelData);

        return $barcode;
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
            'number'      => ['required', 'numeric'],
            'note'        => ['sometimes', 'nullable', 'string', 'max:1000'],
            'data'        => ['sometimes', 'nullable', 'array'],
            'status'      => ['required', Rule::enum(BarcodeStatusEnum::class)],
            'type'        => ['required', Rule::enum(BarcodeTypeEnum::class)],
            'source_id'   => ['sometimes', 'required', 'string', 'max:255'],
            'assigned_at' => ['sometimes', 'nullable', 'date'],
            'fetched_at'  => ['sometimes', 'date'],

        ];
    }

    public function action(Group $group, array $modelData): Barcode
    {

        $this->asAction = true;
        $this->initialisation($group, $modelData);

        return $this->handle($group, $this->validatedData);
    }
}
