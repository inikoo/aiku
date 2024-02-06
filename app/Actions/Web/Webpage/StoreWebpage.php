<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 08:32:10 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage;

use App\Actions\Helpers\Snapshot\StoreWebpageSnapshot;
use App\Actions\OrgAction;
use App\Actions\Web\Webpage\Hydrators\WebpageHydrateUniversalSearch;
use App\Actions\Web\Website\Hydrators\WebsiteHydrateWebpages;
use App\Enums\Web\Webpage\WebpagePurposeEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Models\Web\Website;
use App\Models\Web\Webpage;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreWebpage extends OrgAction
{
    use AsAction;


    private Website $website;

    public function handle(Website $website, array $modelData): Webpage
    {

        data_set($modelData, 'level', $this->getLevel(Arr::get($modelData, 'parent_id')));

        data_set($modelData, 'group_id', $website->group_id);
        data_set($modelData, 'organisation_id', $website->organisation_id);

        /** @var Webpage $webpage */
        $webpage = $website->webpages()->create($modelData);
        $webpage->stats()->create();

        $snapshot = StoreWebpageSnapshot::run(
            $webpage,
            [
                'layout' => [
                    'src'  => null,
                    'html' => ''
                ]
            ],
        );

        $webpage->update(
            [
                'unpublished_snapshot_id' => $snapshot->id,
                'compiled_layout'         => $snapshot->compiledLayout(),

            ]
        );


        WebpageHydrateUniversalSearch::run($webpage);
        WebsiteHydrateWebpages::dispatch($website);

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
        return [
            'url'       => [
                'required','ascii','lowercase','max:255','alpha_dash',
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
            'code'      => [
                'required','ascii','max:64','alpha_dash',
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
        ];
    }

    public function action(Website $website, array $modelData): Webpage
    {
        $this->asAction = true;
        $this->website  = $website;
        $this->initialisationFromShop($website->shop, $modelData);

        return $this->handle($website, $this->validatedData);
    }

    public function getLevel($parent_id): int
    {
        /** @var Webpage $parent */
        if ($parent_id && $parent = Webpage::where('id', $parent_id)->first()) {
            return $parent->level + 1;
        }

        return 1;
    }

}
