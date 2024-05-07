<?php

namespace App\Actions\Manufacturing\RawMaterial;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Manufacturing\RawMaterial\RawMaterialStateEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialStockStatusEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialTypeEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialUnitEnum;
use App\Models\Manufacturing\RawMaterial;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateRawMaterial extends OrgAction
{
    use WithActionUpdate;

    public function handle(RawMaterial $rawMaterial, array $modelData): RawMaterial
    {
        $rawMaterial= $this->update($rawMaterial, $modelData);
        return $rawMaterial;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("inventory.productions.edit");
    }


    public function rules(): array
    {
        return [
            'key'                          => ['integer', 'min:0'],
            'type'                         => [Rule::enum(RawMaterialTypeEnum::class)],
            'type_key'                     => ['integer', 'min:0'],
            'state'                        => [Rule::enum(RawMaterialStateEnum::class)],
            'production_supplier_key'      => ['integer', 'min:0'],
            'creation_date'                => ['date'],
            'code'                         => ['string', 'max:64'],
            'description'                  => ['string', 'max:255'],
            'part_unit_ratio'               => ['numeric', 'min:0'],
            'unit'                         => [Rule::enum(RawMaterialUnitEnum::class)],
            'unit_label'                   => ['string', 'max:64'],
            'unit_cost'                    => ['numeric', 'min:0'],
            'stock'                        => ['numeric', 'min:0'],
            'stock_status'                 => [Rule::enum(RawMaterialStockStatusEnum::class)],
            'production_parts_number'      => ['integer', 'min:0'],
        ];
    }

    public function asController(RawMaterial $rawMaterial, ActionRequest $request): RawMaterial
    {
        $this->rawMaterial = $rawMaterial;
        $this->initialisation($rawMaterial->organisation, $request);


        return $this->handle(
            rawMaterial: $rawMaterial,
            modelData: $this->validatedData
        );
    }

    public function action(RawMaterial $rawMaterial, $modelData): RawMaterial
    {
        $this->asAction   = true;
        $this->rawMaterial = $rawMaterial;
        $this->initialisation($rawMaterial->organisation, $modelData);


        return $this->handle(
            rawMaterial: $rawMaterial,
            modelData: $this->validatedData
        );
    }
}