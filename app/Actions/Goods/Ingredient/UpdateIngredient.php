<?php
/*
 * author Arya Permana - Kirin
 * created on 14-11-2024-14h-16m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Goods\Ingredient;

use App\Actions\GrpAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Goods\Ingredient;
use Lorisleiva\Actions\ActionRequest;

class UpdateIngredient extends GrpAction
{
    use WithActionUpdate;

    public function handle(Ingredient $ingredient, array $modelData): Ingredient
    {
        $ingredient = $this->update($ingredient, $modelData);

        return $ingredient;
    }

    public function rules(): array
    {
        return [
            'name'                 => ['sometimes', 'max:250', 'string'],
            'number_trade_units'   => ['sometimes', 'sometimes'],
            ];
    }

    public function action(Ingredient $ingredient, array $modelData): Ingredient
    {
        $this->asAction       = true;
        $this->initialisation($ingredient->group, $modelData);

        return $this->handle($ingredient, $this->validatedData);
    }

    public function asController(Ingredient $ingredient, ActionRequest $request): Ingredient
    {
        $this->initialisation($ingredient->group, $request);
        return $this->handle($ingredient, $this->validatedData);
    }
}
