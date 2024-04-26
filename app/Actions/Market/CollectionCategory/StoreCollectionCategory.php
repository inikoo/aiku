<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Apr 2024 17:49:52 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\CollectionCategory;

use App\Actions\Market\CollectionCategory\Hydrators\CollectionCategoryHydrateUniversalSearch;
use App\Actions\Market\Shop\Hydrators\ShopHydrateCollectionCategories;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateCollectionCategories;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateCollectionCategories;
use App\Models\Market\CollectionCategory;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class StoreCollectionCategory extends OrgAction
{
    public function handle(Shop $shop, array $modelData): CollectionCategory
    {
        data_set($modelData, 'shop_id', $shop->id);
        data_set($modelData, 'group_id', $shop->group_id);
        data_set($modelData, 'organisation_id', $shop->organisation_id);


        /** @var CollectionCategory $collectionCategory */
        $collectionCategory = $shop->collectionCategories()->create($modelData);

        $collectionCategory->stats()->create();
        $collectionCategory->salesStats()->create();

        CollectionCategoryHydrateUniversalSearch::dispatch($collectionCategory);
        OrganisationHydrateCollectionCategories::dispatch($collectionCategory->organisation)->delay($this->hydratorsDelay);
        GroupHydrateCollectionCategories::dispatch($collectionCategory->group)->delay($this->hydratorsDelay);
        ShopHydrateCollectionCategories::dispatch($collectionCategory->shop)->delay($this->hydratorsDelay);


        return $collectionCategory;
    }

    public function rules(): array
    {
        return [
            'code'                 => [
                'required',
                'max:32',
                new AlphaDashDot(),
                new IUnique(
                    table: 'collection_categories',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ]
                ),
            ],
            'name'                 => ['required', 'max:250', 'string'],
            'image_id'             => ['sometimes', 'required', 'exists:media,id'],
            'description'          => ['sometimes', 'required', 'max:1500'],
            ];
    }

    public function action(Shop $shop, array $modelData, int $hydratorsDelay = 0): CollectionCategory
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($shop, $this->validatedData);
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): CollectionCategory
    {
        $this->initialisationFromShop($shop, $request);
        return $this->handle($shop, $this->validatedData);
    }


}
