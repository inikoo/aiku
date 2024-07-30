<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:14 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Banner;

use App\Actions\Helpers\Snapshot\StoreBannerSnapshot;
use App\Actions\OrgAction;
use App\Actions\Web\Banner\Hydrators\BannerHydrateUniversalSearch;
use App\Actions\Web\Banner\UI\ParseBannerLayout;
use App\Enums\Web\Banner\BannerTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Web\Banner;
use App\Models\Web\Website;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreBanner extends OrgAction
{
    use AsAction;
    use WithAttributes;

    private Shop|Fulfilment $parent;
    private Website $website;
    private string $scope;


    public function handle(Shop|Fulfilment $parent, array $modelData): Banner
    {
        $this->parent = $parent;

        $layout = [
            "delay"      => 5000,
            "navigation" => [
                "bottomNav" => [
                    "value"     => true,
                    "type"      => "bullet"
                ],
                "sideNav" => [
                    "value"     => true,
                    "type"      => "arrow"
                ]
            ],
            "common"     => [
                // "corners"      => [
                //     "bottomLeft" => [
                //         "type" => "slideControls"
                //     ]
                // ],
                "spaceBetween" => 0,
                "centralStage" => [
                    "title"    => null,
                    "subtitle" => null,
                    "text"     => null
                ]
            ],
            "components" => [
            ]
        ];
        list($layout, $slides) = ParseBannerLayout::run($layout);

        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'ulid', Str::ulid());
        data_set($modelData, 'date', now());

        /** @var Banner $banner */
        $banner   = Banner::create($modelData);
        $snapshot = StoreBannerSnapshot::run(
            $banner,
            [
                'layout' => $layout
            ],
            $slides
        );

        $banner->update(
            [
                'unpublished_snapshot_id' => $snapshot->id,
                'compiled_layout'         => $snapshot->compiledLayout()
            ]
        );

        $banner->stats()->create();

        BannerHydrateUniversalSearch::dispatch($banner);

        return $banner;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return true;

        // Not complete yet
        // return $request->get('customerUser')->hasPermissionTo("portfolio.banners.edit");
    }

    /*    public function prepareForValidation(ActionRequest $request): void
        {
            if (!$request->exists('portfolio_website_id')) {
                $count = $this->customer->portfolioWebsites()->count();
                if ($count == 1) {
                    $portfolioWebsite = $request->get('customer')->portfolioWebsites()->first();

                    $request->merge(['portfolio_website_id' => $portfolioWebsite->id]);
                }
            }

            if (!$request->get('name')) {
                $name = PetName::Generate(2, ' ').' banner';
                $request->merge(['name' => $name]);
            }
            if (!$request->get('type')) {
                $request->merge(['type' => BannerTypeEnum::LANDSCAPE->value]);
            }

        }*/

    public function rules(): array
    {
        return [
            'name'                 => ['required', 'string', 'max:255'],
            'type'                 => ['required', new Enum(BannerTypeEnum::class)],
        ];
    }

    public function asController(Shop $shop, Website $website, ActionRequest $request): Banner
    {
        $this->parent  = $shop;
        $this->website = $website;
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData);
    }

    public function inFulfilment(Fulfilment $fulfilment, Website $website, ActionRequest $request): Banner
    {
        $this->parent  = $fulfilment;
        $this->website = $website;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment, $this->validatedData);
    }

    public function action(Website $website, array $objectData): Banner
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);

        $validatedData = $this->validateAttributes();
        return $this->handle($website, $validatedData);
    }

    public function getCommandSignature(): string
    {
        return 'customer:new-banner {customer} {portfolio-website} {--T|type=landscape} {--N|name=}';
    }

    public function asCommand(Command $command): int
    {
        try {
            $customer = Customer::where('slug', $command->argument('customer'))->firstOrFail();
        } catch (Exception) {
            $command->error('Customer not found');

            return 1;
        }
        Config::set('global.customer_id', $customer->id);
        $this->customer=$customer;

        $portfolioWebsite = PortfolioWebsite::where('slug', $command->argument('portfolio-website'))->firstOrFail();


        $this->asAction = true;
        $this->setRawAttributes(
            [
                'name'                 => $command->option('name') ?? PetName::Generate(2).' banner',
                'portfolio_website_id' => $portfolioWebsite->id,
                'type'                 => $command->option('type')
            ]
        );
        $validatedData = $this->validateAttributes();

        $banner = $this->handle($portfolioWebsite ?? $customer, $validatedData);

        $command->info("Done! Banner $banner->slug ($banner->name) created 🎉");

        return 0;
    }


    public function jsonResponse(Banner $banner): string
    {
        return route(
            'grp.org.shops.show.web.banners.workshop',
            [
                'organisation' => $this->parent->organisation->slug,
                'shop'         => $this->parent->slug,
                'website'      => $this->website->slug,
                'banner'       => $banner->slug
            ]
        );
    }

    public function htmlResponse(Banner $banner): RedirectResponse
    {
        return redirect()->route(
            'grp.org.shops.show.web.banners.workshop',
            [
                'organisation' => $this->parent->organisation->slug,
                'shop'         => $this->parent->slug,
                'website'      => $this->website->slug,
                'banner'       => $banner->slug
            ]
        );
    }
}
