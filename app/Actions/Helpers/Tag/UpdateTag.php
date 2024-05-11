<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 15 Nov 2023 12:42:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Tag;

use App\Actions\CRM\Prospect\Tags\Hydrators\TagHydrateUniversalSearch;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Helpers\Tag;
use App\Models\Catalogue\Shop;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateTag
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    private bool $asAction = false;
    private Shop $parent;
    /**
     * @var \App\Models\Helpers\Tag
     */
    private Tag $tag;


    public function handle(Tag $tag, array $modelData): Tag
    {
        if (Arr::has($modelData, 'label')) {
            data_set($modelData, 'name', Arr::get($modelData, 'label'));
        }

        $tag = $this->update($tag, $modelData);


        TagHydrateUniversalSearch::dispatch($tag);

        return $tag;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("crm.prospects.edit");
    }

    public function rules(): array
    {
        return [
            'label' => [
                'required',
                'string',
                new IUnique(
                    table: 'tags',
                    extraConditions: [
                        ['column' => 'type', 'value' => $this->tag->type]
                    ],
                ),
            ],
        ];
    }

    public function inProspect(Tag $tag, ActionRequest $request): Tag
    {
        $this->tag = $tag;
        $this->fillFromRequest($request);

        return $this->handle($tag, $this->validateAttributes());
    }

    public function inShop(Shop $shop, Tag $tag, ActionRequest $request): Tag
    {
        $this->parent = $shop;
        $this->tag    = $tag;
        $this->fillFromRequest($request);

        return $this->handle($tag, $this->validateAttributes());
    }
}
