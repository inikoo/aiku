<?php

/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 08:32:10 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage;

use App\Actions\Helpers\Snapshot\StoreWebpageSnapshot;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateWebpages;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateWebpages;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Web\Webpage\Hydrators\WebpageHydrateChildWebpages;
use App\Actions\Web\Webpage\Search\WebpageRecordSearch;
use App\Actions\Web\Website\Hydrators\WebsiteHydrateWebpages;
use App\Enums\Web\Webpage\WebpageSubTypeEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Models\Web\Website;
use App\Models\Web\Webpage;
use App\Rules\AlphaDashSlash;
use App\Rules\IUnique;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreWebpage extends OrgAction
{
    use AsAction;
    use WithNoStrictRules;

    private Website $website;

    private Webpage|Website $parent;

    /**
     * @throws \Throwable
     */
    public function handle(Website|Webpage $parent, array $modelData): Webpage
    {
        data_set($modelData, 'url', '', overwrite: false);
        data_set($modelData, 'shop_id', $parent->shop_id);

        if ($parent instanceof Webpage) {
            data_set($modelData, 'website_id', $parent->website_id);
            data_set($modelData, 'level', $parent->level + 1);
        } else {
            data_set($modelData, 'level', 1);
        }


        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);

        $webpage = DB::transaction(function () use ($parent, $modelData) {
            /** @var Webpage $webpage */
            $webpage = $parent->webpages()->create($modelData);
            $webpage->stats()->create();
            $webpage->refresh();

            $snapshot = StoreWebpageSnapshot::run(
                $webpage,
                [
                    'layout' => [
                        'web_blocks' => []
                    ]
                ],
            );

            $webpage->update(
                [
                    'unpublished_snapshot_id' => $snapshot->id,
                ]
            );

            return $webpage;
        });


        WebpageRecordSearch::dispatch($webpage);
        GroupHydrateWebpages::dispatch($webpage->group)->delay($this->hydratorsDelay);
        OrganisationHydrateWebpages::dispatch($webpage->organisation)->delay($this->hydratorsDelay);
        WebsiteHydrateWebpages::dispatch($webpage->website)->delay($this->hydratorsDelay);
        if ($webpage->parent_id) {
            WebpageHydrateChildWebpages::dispatch($webpage->parent)->delay($this->hydratorsDelay);
        }

        return $webpage;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("web.{$this->shop->id}.edit");
    }

    public function rules(): array
    {
        $rules = [
            'url'         => [
                'sometimes',
                'required',
                'ascii',
                'lowercase',
                'max:255',
                new AlphaDashSlash(),
                new IUnique(
                    table: 'webpages',
                    extraConditions: [
                        [
                            'column' => 'website_id',
                            'value'  => $this->website->id
                        ],
                    ]
                ),
            ],
            'code'        => [
                'required',
                'ascii',
                'max:64',
                'alpha_dash',
                new IUnique(
                    table: 'webpages',
                    extraConditions: [
                        ['column' => 'website_id', 'value' => $this->website->id],
                    ]
                ),
            ],
            'sub_type'    => ['required', Rule::enum(WebpageSubTypeEnum::class)],
            'type'        => ['required', Rule::enum(WebpageTypeEnum::class)],
            'state'       => ['sometimes', Rule::enum(WebpageStateEnum::class)],
            'is_fixed'    => ['sometimes', 'boolean'],
            'ready_at'    => ['sometimes', 'date'],
            'live_at'     => ['sometimes', 'date'],
            'model_type'  => ['sometimes', 'string'],
            'model_id'    => ['sometimes', 'integer'],
            'title'       => ['required', 'string'],
            'description' => ['sometimes', 'string'],


        ];

        if ($this->parent instanceof Webpage) {
            $rules['url'] = [
                'required',
                'ascii',
                'lowercase',
                'max:255',
                new AlphaDashSlash(),
                new IUnique(
                    table: 'webpages',
                    extraConditions: [
                        [
                            'column' => 'website_id',
                            'value'  => $this->website->id
                        ],
                    ]
                ),
            ];
        }

        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);
            $rules['migration_data'] = ['sometimes', 'array'];
        }

        return $rules;
    }


    /**
     * @throws \Throwable
     */
    public function action(Website|Webpage $parent, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): Webpage
    {
        if (!$audit) {
            Webpage::disableAuditing();
        }

        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->parent         = $parent;
        $this->website        = $parent instanceof Website ? $parent : $parent->website;
        $this->initialisationFromShop($this->website->shop, $modelData);

        return $this->handle($parent, $this->validatedData);
    }


}
