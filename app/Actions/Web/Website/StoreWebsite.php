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
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\Web\Website\WebsiteEngineEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Enums\Web\Website\WebsiteTypeEnum;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Catalogue\Shop;
use App\Models\Web\Website;
use App\Rules\IUnique;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;
use Illuminate\Console\Command;

class StoreWebsite extends OrgAction
{
    private Fulfilment|Shop $parent;

    public function handle(Shop $shop, array $modelData): Website
    {
        data_set($modelData, 'group_id', $shop->group_id);
        data_set($modelData, 'organisation_id', $shop->organisation_id);
        data_set(
            $modelData,
            'type',
            match ($shop->type) {
                ShopTypeEnum::FULFILMENT   => WebsiteTypeEnum::FULFILMENT,
                ShopTypeEnum::DROPSHIPPING => WebsiteTypeEnum::DROPSHIPPING,
                ShopTypeEnum::B2B          => WebsiteTypeEnum::B2B,
                ShopTypeEnum::B2C          => WebsiteTypeEnum::B2C,
            }
        );
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
        $website->webStats()->create();
        //AddWebsiteToCloudflare::run($website);

        OrganisationHydrateWeb::dispatch($shop->organisation);
        WebsiteHydrateUniversalSearch::dispatch($website);

        if($website->engine === WebsiteEngineEnum::AIKU) {
            $website= SeedWebsiteFixedWebpages::run($website);
        }


        return $website;

    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if($this->parent instanceof Fulfilment) {
            return $request->user()->hasPermissionTo("fulfilment-shop.{$this->parent->id}.edit");
        } elseif ($this->parent instanceof Shop) {
            return $request->user()->hasPermissionTo("web.{$this->parent->id}.edit");
        }
        return false;
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

        if($this->parent instanceof Fulfilment) {
            return Redirect::route('grp.org.fulfilments.show.web.websites.show', [
                $this->organisation->slug,
                $this->parent->slug,
                $website->slug
            ]);
        }

        return Redirect::route('grp.org.shops.show.web.websites.show', [
            $this->organisation->slug,
            $this->parent->slug,
            $website->slug
        ]);
    }

    public function asController(Shop $shop, ActionRequest $request): Website
    {
        $this->parent=$shop;
        $this->shop  = $shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData);
    }

    public function inFulfilment(Fulfilment $fulfilment, ActionRequest $request): Website
    {
        $this->parent=$fulfilment;
        $this->shop  = $fulfilment->shop;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment->shop, $this->validatedData);
    }

    public function action(Shop $shop, array $modelData): Website
    {
        $this->asAction = true;
        $this->shop     = $shop;
        $this->initialisation($shop->organisation, $modelData);

        return $this->handle($shop, $this->validatedData);
    }

    public string $commandSignature = 'website:create {shop : shop slug} {domain} {code} {name}';

    public function asCommand(Command $command): int
    {
        $this->asAction = true;

        try {
            $shop = Shop::where('slug', $command->argument('shop'))->firstOrFail();
        } catch (Exception) {
            $command->error('Shop not found');

            return 1;
        }
        $this->organisation = $shop->organisation;
        $this->shop         =$shop;
        if($shop->type === 'fulfilment') {
            $this->parent=$shop->fulfilment;
        } else {
            $this->parent=$shop;
        }


        $this->setRawAttributes([
            'domain'      => $command->argument('domain'),
            'code'        => $command->argument('code'),
            'name'        => $command->argument('name'),
        ]);


        try {
            $validatedData = $this->validateAttributes();
        } catch (Exception $e) {
            $command->error($e->getMessage());
            return 1;
        }

        $website = $this->handle($shop, $validatedData);

        $command->info("Website $website->code [$website->domain] created successfully 🎉");

        return 0;
    }
}
