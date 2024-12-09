<?php

/*
 * author Arya Permana - Kirin
 * created on 16-10-2024-15h-33m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Helpers\Tag;

use App\Actions\Helpers\Tag\Hydrators\TagHydrateSubjects;
use App\Actions\CRM\Prospect\Tags\Hydrators\TagHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Models\Catalogue\Product;
use App\Models\CRM\Prospect;
use App\Models\Helpers\Tag;
use App\Models\Inventory\Location;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class SyncTagsModel extends OrgAction
{
    use AsAction;
    use WithAttributes;

    public function handle(Product|Prospect|Location $parent, array $modelData)
    {
        // dd($modelData);
        $oldTags = $parent->tags()->pluck('id');


        $parent->syncTagsWithType(
            Arr::get($modelData, 'tags', []),
            Arr::get($modelData, 'type')
        );

        $currentTags = $parent->tags()->pluck('id');

        $newTags      = $currentTags->diff($oldTags);
        $removedTags  = $oldTags->diff($currentTags);
        $affectedTags = $newTags->merge($removedTags);

        foreach ($affectedTags as $tagId) {
            $tag = Tag::find($tagId);
            TagHydrateSubjects::dispatch($tag);
            TagHydrateUniversalSearch::dispatch($tag);
        }


        return $parent;
    }

    public function authorize(ActionRequest $request): bool
    {
        return true;
    }

    public function rules(ActionRequest $request): array
    {
        return [
            'tags'   => ['nullable', 'array'],
            'tags.*' => ['string'],
            'type'   => ['nullable', 'string']
        ];
    }


    public function inProduct(Product $product, ActionRequest $request): Product
    {
        $this->fillFromRequest($request);
        $this->fill(['type' => 'catalogue']);
        $this->initialisationFromShop($product->shop, $request);

        return $this->handle($product, $this->trimTags($this->validateAttributes()));
    }

    public function inProspect(Prospect $prospect, ActionRequest $request)
    {
        $this->fillFromRequest($request);
        $this->fill(['type' => 'crm']);
        $this->initialisationFromShop($prospect->shop, $request);

        $this->handle($prospect, $this->trimTags($this->validateAttributes()));
    }

    public function inLocation(Location $location, ActionRequest $request): Location
    {
        $this->fillFromRequest($request);
        $this->fill(['type' => 'inventory']);
        $this->initialisationFromWarehouse($location->warehouse, $request);

        return $this->handle($location, $this->trimTags($this->validateAttributes()));
    }

    public function action(Product $product, array $modelData): Product
    {
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($product, $this->trimTags($validatedData));
    }

    private function trimTags(array $modelData): array
    {
        if (is_array($modelData['tags'])) {

            data_set($modelData, 'tags', array_map('trim', $modelData['tags']));
            data_set($modelData, 'tags', array_intersect_key($modelData['tags'], array_unique(array_map('strtolower', $modelData['tags']))));

        }
        return $modelData;
    }
}
