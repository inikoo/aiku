<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 06:49:35 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Collection;

use App\Actions\Market\Collection\Hydrators\CollectionHydrateUniversalSearch;
use App\Actions\Market\CollectionCategory\Hydrators\CollectionCategoryHydrateCollections;
use App\Actions\Market\Shop\Hydrators\ShopHydrateCollections;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateCollections;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateCollections;
use App\Models\Market\Collection;
use App\Models\Market\CollectionCategory;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class StoreCollection extends OrgAction
{
    public function handle(Shop|CollectionCategory $parent, array $modelData): Collection
    {

        if($parent instanceof CollectionCategory) {
            $shop = $parent->shop;
        } else {
            $shop = $parent;
        }

        data_set($modelData, 'shop_id', $shop->id);
        data_set($modelData, 'group_id', $shop->group_id);
        data_set($modelData, 'organisation_id', $shop->organisation_id);


        /** @var Collection $collection */
        $collection = $parent->collections()->create($modelData);

        $collection->stats()->create();
        $collection->salesIntervals()->create();

        CollectionHydrateUniversalSearch::dispatch($collection);
        OrganisationHydrateCollections::dispatch($collection->organisation)->delay($this->hydratorsDelay);
        GroupHydrateCollections::dispatch($collection->group)->delay($this->hydratorsDelay);
        ShopHydrateCollections::dispatch($collection->shop)->delay($this->hydratorsDelay);


        if($parent instanceof CollectionCategory) {
            CollectionCategoryHydrateCollections::dispatch($parent)->delay($this->hydratorsDelay);
        }

        return $collection;
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

    public function action(Shop|CollectionCategory $parent, array $modelData, int $hydratorsDelay = 0): Collection
    {
        if($parent instanceof CollectionCategory) {
            $shop = $parent->shop;
        } else {
            $shop = $parent;
        }

        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($parent, $this->validatedData);
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): Collection
    {
        $this->initialisationFromShop($shop, $request);
        return $this->handle($shop, $this->validatedData);
    }


}
