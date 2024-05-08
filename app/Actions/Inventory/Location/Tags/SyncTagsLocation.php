<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 08:23:57 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location\Tags;

use App\Actions\Helpers\Tag\Hydrators\TagHydrateSubjects;
use App\Actions\CRM\Prospect\Tags\Hydrators\TagHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Models\Helpers\Tag;
use App\Models\Inventory\Location;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class SyncTagsLocation extends OrgAction
{
    use AsAction;
    use WithAttributes;

    public function handle(Location $location, array $modelData): Location
    {
        $oldTags = $location->tags()->pluck('id');


        $location->syncTagsWithType(
            Arr::get($modelData, 'tags', []),
            Arr::get($modelData, 'type')
        );

        $currentTags = $location->tags()->pluck('id');

        $newTags      = $currentTags->diff($oldTags);
        $removedTags  = $oldTags->diff($currentTags);
        $affectedTags = $newTags->merge($removedTags);

        foreach ($affectedTags as $tagId) {
            $tag = Tag::find($tagId);
            TagHydrateSubjects::dispatch($tag);
            TagHydrateUniversalSearch::dispatch($tag);
        }


        return $location;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("locations.{$this->warehouse->id}.edit");

        return $request->user()->hasPermissionTo("locations.{$this->warehouse->id}.edit");
    }

    public function rules(ActionRequest $request): array
    {
        return [
            'tags'   => ['nullable', 'array'],
            'tags.*' => ['string'],
            'type'   => ['nullable', 'string']
        ];
    }


    public function asController(Location $location, ActionRequest $request): Location
    {
        $this->fillFromRequest($request);
        $this->fill(['type' => 'crm']);
        $this->initialisationFromWarehouse($location->warehouse, $request);

        return $this->handle($location, $this->trimTags($this->validateAttributes()));
    }

    public function action(Location $location, array $modelData): Location
    {
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($location, $this->trimTags($validatedData));
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
