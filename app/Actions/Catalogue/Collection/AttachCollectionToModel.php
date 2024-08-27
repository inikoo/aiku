<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 06:49:35 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Collection;

use App\Actions\OrgAction;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Catalogue\Collection;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;

class AttachCollectionToModel extends OrgAction
{
    public function handle(Product|ProductCategory|Collection $model, Collection $collection): Collection
    {
        if($model instanceof ProductCategory) {
            if($model->type == ProductCategoryTypeEnum::DEPARTMENT) {
                $model->collections()->attach($collection->id, [
                    'type' => 'Department',
                ]);
            }

            if($model->type == ProductCategoryTypeEnum::FAMILY) {
                $model->collections()->attach($collection->id, [
                    'type' => 'Family',
                ]);
            }
        } elseif ($model instanceof Product) {
            $model->collections()->attach($collection->id, [
                'type' => 'Product',
            ]);
        } else {
            $model->inCollections()->attach($collection->id, [
                'type' => 'Collection',
            ]);
        }

        return $collection;
    }

    public function action(Product|ProductCategory|Collection $model, Collection $collection): Collection
    {
        $this->asAction       = true;
        $this->initialisationFromShop($model->shop, []);

        return $this->handle($model, $collection);
    }
}
