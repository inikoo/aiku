<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 07:51:01 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Collection;

use App\Actions\Market\Collection\Hydrators\CollectionHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Market\CollectionResource;
use App\Models\Market\Collection;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class UpdateCollection extends OrgAction
{
    use WithActionUpdate;

    private Collection $collection;

    public function handle(Collection $collection, array $modelData): Collection
    {
        $collection = $this->update($collection, $modelData, ['data']);
        CollectionHydrateUniversalSearch::dispatch($collection);

        return $collection;
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
                        ['column' => 'id', 'value' => $this->collection->id, 'operator' => '!=']

                    ]
                ),
            ],
            'name'        => ['sometimes', 'max:250', 'string'],
            'image_id'    => ['sometimes', 'required', 'exists:media,id'],
            'description' => ['sometimes', 'required', 'max:1500'],

        ];
    }

    public function action(Collection $collection, array $modelData): Collection
    {
        $this->asAction   = true;
        $this->collection = $collection;
        $this->initialisationFromShop($collection->shop, $modelData);

        return $this->handle($collection, $this->validatedData);
    }

    public function asController(Organisation $organisation, Shop $shop, Collection $collection, ActionRequest $request): Collection
    {
        $this->collection = $collection;

        $this->initialisationFromShop($shop, $request);

        return $this->handle($collection, $this->validatedData);
    }

    public function jsonResponse(Collection $collection): CollectionResource
    {
        return new CollectionResource($collection);
    }
}
