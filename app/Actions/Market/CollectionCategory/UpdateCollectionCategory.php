<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 07:51:01 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\CollectionCategory;

use App\Actions\Market\CollectionCategory\Hydrators\CollectionCategoryHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Market\CollectionCategoryResource;
use App\Models\Market\CollectionCategory;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class UpdateCollectionCategory extends OrgAction
{
    use WithActionUpdate;

    private CollectionCategory $collectionCategory;

    public function handle(CollectionCategory $collectionCategory, array $modelData): CollectionCategory
    {
        $collectionCategory = $this->update($collectionCategory, $modelData, ['data']);
        CollectionCategoryHydrateUniversalSearch::dispatch($collectionCategory);

        return $collectionCategory;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("products.{$this->shop->id}.edit");
    }

    public function rules(): array
    {
        return [
            'code'        => [
                'sometimes',
                'max:32',
                new AlphaDashDot(),
                new IUnique(
                    table: 'product_categories',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                        ['column' => 'id', 'value' => $this->collectionCategory->id, 'operator' => '!=']

                    ]
                ),
            ],
            'name'        => ['sometimes', 'max:250', 'string'],
            'image_id'    => ['sometimes', 'required', 'exists:media,id'],
            'description' => ['sometimes', 'required', 'max:1500'],

        ];
    }

    public function action(CollectionCategory $collectionCategory, array $modelData): CollectionCategory
    {
        $this->asAction           = true;
        $this->collectionCategory = $collectionCategory;
        $this->initialisationFromShop($collectionCategory->shop, $modelData);

        return $this->handle($collectionCategory, $this->validatedData);
    }

    public function asController(Organisation $organisation, Shop $shop, CollectionCategory $collectionCategory, ActionRequest $request): CollectionCategory
    {
        $this->collectionCategory = $collectionCategory;

        $this->initialisationFromShop($shop, $request);

        return $this->handle($collectionCategory, $this->validatedData);
    }

    public function jsonResponse(CollectionCategory $collectionCategory): CollectionCategoryResource
    {
        return new CollectionCategoryResource($collectionCategory);
    }
}
