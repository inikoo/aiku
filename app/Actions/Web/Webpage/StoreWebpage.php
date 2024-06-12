<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 08:32:10 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage;

use App\Actions\Helpers\Snapshot\StoreWebpageSnapshot;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateWebpages;
use App\Actions\Web\Webpage\Hydrators\WebpageHydrateUniversalSearch;
use App\Actions\Web\Webpage\Hydrators\WebpageHydrateWebpages;
use App\Actions\Web\Website\Hydrators\WebsiteHydrateWebpages;
use App\Enums\Web\Webpage\WebpagePurposeEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Models\Web\Website;
use App\Models\Web\Webpage;
use App\Rules\AlphaDashSlash;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreWebpage extends OrgAction
{
    use AsAction;


    private Website $website;

    private Webpage|Website $parent;

    public function handle(Website|Webpage $parent, array $modelData): Webpage
    {
        data_set($modelData, 'url', '', overwrite: false);

        if ($parent instanceof Webpage) {
            data_set($modelData, 'website_id', $parent->website_id);
            data_set($modelData, 'level', $parent->level + 1);
        } else {
            data_set($modelData, 'level', 1);
        }


        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);

        /** @var Webpage $webpage */
        $webpage = $parent->webpages()->create($modelData);
        $webpage->stats()->create();

        $snapshot = StoreWebpageSnapshot::run(
            $webpage,
            [
                'layout' => [
                    'blocks'=> []
                ]
            ],
        );

        $webpage->update(
            [
                'unpublished_snapshot_id' => $snapshot->id,

            ]
        );



        WebpageHydrateUniversalSearch::dispatch($webpage);
        OrganisationHydrateWebpages::dispatch($webpage->organisation);

        WebsiteHydrateWebpages::dispatch($webpage->website);
        if ($webpage->parent_id) {
            WebpageHydrateWebpages::dispatch($webpage->parent);
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
            'code'      => [
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
            'source_id' => ['sometimes', 'string'],
            'purpose'   => ['required', Rule::enum(WebpagePurposeEnum::class)],
            'type'      => ['required', Rule::enum(WebpageTypeEnum::class)],
            'state'     => ['sometimes', Rule::enum(WebpageStateEnum::class)],
            'is_fixed'  => ['sometimes', 'boolean'],
            'ready_at'  => ['sometimes', 'date'],
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

        return $rules;
    }


    public function action(Website|Webpage $parent, array $modelData): Webpage
    {
        $this->asAction = true;
        $this->parent   = $parent;
        $this->website  = $parent instanceof Website ? $parent : $parent->website;
        $this->initialisationFromShop($this->website->shop, $modelData);

        return $this->handle($parent, $this->validatedData);
    }


}
