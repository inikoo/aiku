<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 08:23:57 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect\Tags;

use App\Actions\Helpers\Tag\Hydrators\TagHydrateProspects;
use App\Actions\Helpers\Tag\Hydrators\TagHydrateSubjects;
use App\Actions\CRM\Prospect\Tags\Hydrators\TagHydrateUniversalSearch;
use App\Models\Helpers\Tag;
use App\Models\CRM\Prospect;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class SyncTagsCustomer
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    public function handle(Prospect $prospect, array $modelData): Prospect
    {
        $oldTags = $prospect->tags()->pluck('id');


        $prospect->syncTagsWithType(
            Arr::get($modelData, 'tags', []),
            Arr::get($modelData, 'type')
        );

        $currentTags = $prospect->tags()->pluck('id');

        $newTags      = $currentTags->diff($oldTags);
        $removedTags  = $oldTags->diff($currentTags);
        $affectedTags = $newTags->merge($removedTags);

        foreach ($affectedTags as $tagId) {
            $tag = Tag::find($tagId);
            TagHydrateSubjects::dispatch($tag);
            TagHydrateProspects::dispatch($tag);
            TagHydrateUniversalSearch::dispatch($tag);
        }


        return $prospect;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("crm.prospects.edit");
    }

    public function rules(ActionRequest $request): array
    {
        return [
            'tags'   => ['nullable', 'array'],
            'tags.*' => ['string'],
            'type'   => ['nullable', 'string']
        ];
    }


    public function asController(Prospect $prospect, ActionRequest $request): Prospect
    {
        $this->fillFromRequest($request);
        $this->fill(['type' => 'crm']);

        return $this->handle($prospect, $this->trimTags($this->validateAttributes()));
    }

    public function action(Prospect $prospect, array $modelData): Prospect
    {
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($prospect, $this->trimTags($validatedData));
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
