<?php
/*
 * author Arya Permana - Kirin
 * created on 14-11-2024-14h-35m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Goods\Ingredient;

use App\Actions\Catalogue\Collection\Hydrators\CollectionHydrateItems;
use App\Actions\GrpAction;
use App\Actions\OrgAction;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Catalogue\Collection;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use App\Models\Goods\Ingredient;
use App\Models\Goods\TradeUnit;
use App\Models\SupplyChain\Stock;

class DetachIngredientFromModel extends GrpAction
{
    public function handle(Product|Stock|TradeUnit $model, Ingredient $ingredient)
    {
        $ingredient = $model->ingredients()->detach($ingredient->id);
    }

    public function action(Product|Stock|TradeUnit $model, Ingredient $ingredient)
    {
        $this->asAction       = true;
        $this->initialisation($model->group, []);

        return $this->handle($model, $ingredient);
    }
}
