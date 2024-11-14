<?php
/*
 * author Arya Permana - Kirin
 * created on 14-11-2024-14h-10m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Goods\Ingredient;

use App\Actions\GrpAction;
use App\Models\Goods\Ingredient;
use App\Models\SysAdmin\Group;
use Lorisleiva\Actions\ActionRequest;

class StoreIngredient extends GrpAction
{
    public function handle(Group $group, array $modelData): Ingredient
    {
        $ingredient = $group->ingredients()->create($modelData);

        return $ingredient;
    }

    public function rules(): array
    {
        return [
            'name'                 => ['required', 'max:250', 'string'],
            'number_trade_units'   => ['sometimes', 'required'],
            ];
    }

    public function action(Group $group, array $modelData): Ingredient
    {
        $this->asAction       = true;
        $this->initialisation($group, $modelData);

        return $this->handle($group, $this->validatedData);
    }

    public function asController(Group $group, ActionRequest $request): Ingredient
    {
        $this->initialisation($group, $request);
        return $this->handle($group, $this->validatedData);
    }
}
