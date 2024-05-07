<?php

namespace App\Actions\Manufacturing\RawMaterial;

use App\Actions\OrgAction;
use App\Enums\Manufacturing\RawMaterial\RawMaterialStateEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialStockStatusEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialTypeEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialUnitEnum;
use App\Models\Manufacturing\RawMaterial;
use App\Models\SysAdmin\Organisation;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreRawMaterial extends OrgAction 
{
    use AsAction;

    public function handle(Organisation $organisation, $modelData): RawMaterial
    {
        data_set($modelData, 'group_id', $organisation->group_id);
        /** @var RawMaterial $rawMaterial */
        $rawMaterial = $organisation->rawMaterials()->create($modelData);
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
            'key'                          => ['required', 'integer', 'min:0'],
            'type'                         => ['required', Rule::enum(RawMaterialTypeEnum::class)],
            'type_key'                     => ['required', 'integer', 'min:0'],
            'state'                        => ['required',  Rule::enum(RawMaterialStateEnum::class)],
            'production_supplier_key'      => ['required', 'integer', 'min:0'],
            'creation_date'                => ['required', 'date'],
            'code'                         => ['required', 'string', 'max:64'],
            'description'                  => ['required', 'string', 'max:255'],
            'part_unit_ratio'              => ['required', 'numeric', 'min:0'],
            'unit'                         => ['required',  Rule::enum(RawMaterialUnitEnum::class)],
            'unit_label'                   => ['required', 'string', 'max:64'],
            'unit_cost'                    => ['required', 'numeric', 'min:0'],
            'stock'                        => ['required', 'numeric', 'min:0'],
            'stock_status'                 => ['sometimes',  Rule::enum(RawMaterialStockStatusEnum::class)],
            'production_parts_number'      => ['required', 'integer', 'min:0'],
        ];
    }

    public function action(Organisation $organisation, array $modelData): RawMaterial
    {
        $this->asAction = true;
        $this->initialisation($organisation, $modelData);

        return $this->handle($organisation, $this->validatedData);
    }

    public function asController(Organisation $organisation, ActionRequest $request): RawMaterial
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation, $this->validatedData);
    }
}
