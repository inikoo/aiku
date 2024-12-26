<?php

/*
 * author Arya Permana - Kirin
 * created on 14-11-2024-14h-30m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Goods\Ingredient;

use App\Actions\GrpAction;
use App\Models\Catalogue\Product;
use App\Models\Goods\Ingredient;
use App\Models\Goods\Stock;
use App\Models\Goods\TradeUnit;

class AttachIngredientToModel extends GrpAction
{
    public function handle(Product|Stock|TradeUnit $model, Ingredient $ingredient)
    {
        $ingredient = $model->ingredients()->attach($ingredient->id);
    }

    public function action(Product|Stock|TradeUnit $model, Ingredient $ingredient)
    {
        $this->asAction       = true;
        $this->initialisation($model->group, []);

        return $this->handle($model, $ingredient);
    }
}
