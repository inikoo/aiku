<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 06:49:35 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Collection;

use App\Actions\Catalogue\Collection\Hydrators\CollectionHydrateItems;
use App\Actions\OrgAction;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Catalogue\Collection;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class DetachModelFromCollection extends OrgAction
{
    public function handle(Collection $collection, array $modelData): Collection
    {
        $modelTypes = [
            'product'    => Product::class,
            'family'    => ProductCategory::class,
            'department' => ProductCategory::class,
            'collection' => Collection::class,
        ];

        foreach ($modelTypes as $key => $modelClass) {
            $id = Arr::get($modelData, $key);

                $model = $modelClass::find($id);
                if ($model) {
                    $this->detachModel($collection, $model);
                }
        }

        CollectionHydrateItems::dispatch($collection);
        return $collection;
    }

    private function detachModel(Collection $collection, Product|ProductCategory|Collection $model)
    {
        if ($model instanceof ProductCategory) {
            if ($model->type == ProductCategoryTypeEnum::DEPARTMENT) {
                $collection->departments()->detach($model->id);
            }

            if ($model->type == ProductCategoryTypeEnum::FAMILY) {
                $collection->families()->detach($model->id);
            }
        } elseif ($model instanceof Product) {
            $collection->products()->detach($model->id);
        } else {
            $collection->collections()->detach($model->id);
        }
    }

    public function action(Collection $collection, array $modelData): Collection
    {
        $this->asAction       = true;
        $this->initialisationFromShop($collection->shop, $modelData);

        return $this->handle($collection, $modelData);
    }

    public function asController(Collection $collection, ActionRequest $request)
    {
        $this->initialisationFromShop($collection->shop, $request);
        return $this->handle($collection, $request->all());
    }
}
