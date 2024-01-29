<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 11:30:40 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Actions\Helpers\Snapshot\StoreWebsiteSnapshot;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateWeb;
use App\Actions\Web\Website\Hydrators\WebsiteHydrateUniversalSearch;
use App\Enums\Web\Website\WebsiteEngineEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Models\Market\Shop;
use App\Models\Web\Website;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class StoreWebsite extends OrgAction
{
    public function handle(Shop $shop, array $modelData): Website
    {
        data_set($modelData, 'group_id', $shop->group_id);
        data_set($modelData, 'organisation_id', $shop->organisation_id);

        data_set($modelData, 'type', $shop->type);
        /** @var Website $website */
        $website = $shop->website()->create($modelData);
        $website->webStats()->create();
        $website->refresh();

        $headerSnapshot = StoreWebsiteSnapshot::run(
            $website,
            [
                'scope'  => 'header',
                'layout' => json_decode(
                    Storage::disk('datasets')->get('website/header.json'),
                    true
                )
            ]
        );
        $footerSnapshot = StoreWebsiteSnapshot::run(
            $website,
            [
                'scope'  => 'footer',
                'layout' => [
                    'src'  => null,
                    'html' => ''

                ]
            ],
        );

        $website->update(
            [
                'unpublished_header_snapshot_id' => $headerSnapshot->id,
                'unpublished_footer_snapshot_id' => $footerSnapshot->id,
                'compiled_layout'                => [
                    'header' => $headerSnapshot->compiledLayout(),
                    'footer' => $footerSnapshot->compiledLayout()
                ]
            ]
        );


        SetInitialWebsiteLogo::dispatch($website);
        $website->webStats()->create();


        //AddWebsiteToCloudflare::run($website);

        OrganisationHydrateWeb::dispatch($shop->organisation);
        WebsiteHydrateUniversalSearch::dispatch($website);

        return SeedWebsiteFixedWebpages::run($website);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("shops.edit");
    }

    public function rules(): array
    {
        return [
            'domain'      => [
                'required',
                'string',
                'ascii',
                'lowercase',
                'max:255',
                new IUnique(
                    table: 'websites',
                    extraConditions: [
                        [
                            'column' => 'group_id',
                            'value'  => $this->organisation->group_id
                        ],
                        [
                            'column'    => 'status',
                            'operation' => '=',
                            'value'     => true
                        ],
                    ]
                ),
            ],
            'code'        => [
                'required',
                'ascii',
                'lowercase',
                'max:64',
                'alpha_dash',
                new IUnique(
                    table: 'websites',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->organisation->group_id],
                    ]
                ),
            ],
            'name'        => ['required', 'string', 'max:255'],
            'source_id'   => ['sometimes', 'string'],
            'created_at'  => ['sometimes', 'date'],
            'launched_at' => ['sometimes', 'date'],
            'state'       => ['sometimes', Rule::enum(WebsiteStateEnum::class)],
            'status'      => ['sometimes', 'boolean'],
            'engine'      => ['sometimes', Rule::enum(WebsiteEngineEnum::class)],
        ];
    }

    public function prepareForValidation(): void
    {
        if ($this->has('domain')) {
            $this->set('domain', preg_replace('/^https?\/\//', '', $this->get('domain')));
        }
    }

    public function afterValidator(Validator $validator): void
    {
        if ($this->shop->website) {
            $validator->errors()->add('domain', 'This shop already have a website');
        }
    }

    public function htmlResponse(Website $website): RedirectResponse
    {
        return Redirect::route('grp.websites.show', [
            $website->slug
        ]);
    }

    public function asController(Shop $shop, ActionRequest $request): Website
    {
        $this->shop = $shop;
        $this->initialisation($shop->organisation, $request);

        return $this->handle($shop, $this->validatedData);
    }

    public function action(Shop $shop, array $modelData): Website
    {
        $this->asAction = true;
        $this->shop     = $shop;
        $this->initialisation($shop->organisation, $modelData);

        return $this->handle($shop, $this->validatedData);
    }
}
