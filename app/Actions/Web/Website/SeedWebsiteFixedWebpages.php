<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:35:41 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Web\Webpage\StoreWebpage;
use App\Enums\Web\Webpage\WebpageSubTypeEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;

class SeedWebsiteFixedWebpages extends OrgAction
{
    use WithActionUpdate;


    public function handle(Website $website): Website
    {
        $storefrontData = [
            'code'     => 'storefront',
            'title'    => 'Home',
            'type'     => WebpageTypeEnum::STOREFRONT,
            'sub_type' => WebpageSubTypeEnum::STOREFRONT,
            'is_fixed' => true,
            'state'    => WebpageStateEnum::READY,
            'ready_at' => now(),
        ];

        if ($website->state == WebsiteStateEnum::LIVE) {
            unset($storefrontData['ready_at']);
            $storefrontData['state']   = WebpageStateEnum::LIVE;
            $storefrontData['live_at'] = now();
        }

        $storefront = $website->webpages()->where('code', 'storefront')->first();

        if (!$storefront) {
            $storefront = StoreWebpage::make()->action($website, $storefrontData);
            $website->updateQuietly(
                [
                    'storefront_id' => $storefront->id
                ]
            );
        }

        $filename = 'webpages/'.$website->type->value.'-webpages.json';
        if (Storage::disk('datasets')->exists($filename)) {
            $webpagesData = Storage::disk('datasets')->json($filename);
            $this->addWebpages($storefront, $webpagesData);
        }
        $filename = 'webpages/'.$website->type->value.'-operations.json';
        if (Storage::disk('datasets')->exists($filename)) {
            foreach (Storage::disk('datasets')->json($filename) as $webpageData) {
                $this->addOperationWebpages($website, $webpageData);
            }
        }

        return $website;
    }

    private function addOperationWebpages(Website $website, $webpageData): void
    {
        $webpage = $website->webpages()->where('code', $webpageData['code'])->first();

        $webpageData = array_merge(
            $webpageData,
            $this->getBaseData($website)
        );
        if (!$webpage) {
            $webpage = StoreWebpage::make()->action($website, $webpageData);
            $website->updateQuietly(
                [
                    $webpageData['code'].'_id' => $webpage->id
                ]
            );
        }
    }


    private function addWebpages(Webpage $parent, $webpagesData): void
    {
        foreach ($webpagesData as $webpageData) {
            $this->addWebpage($parent, $webpageData);
        }
    }

    private function addWebpage(Webpage $parent, $webpageData): void
    {
        $webpageData = array_merge(
            $webpageData,
            $this->getBaseData($parent->website)
        );

        $children = Arr::pull($webpageData, 'children', []);

        $webpage = $parent->website->webpages()->where('code', $webpageData['code'])->first();
        if (!$webpage) {
            $webpage = StoreWebpage::make()->action($parent, $webpageData);
        }

        $this->addWebpages($webpage, $children);
    }


    private function getBaseData(Website $website): array
    {
        $webpageData = [
            'is_fixed' => true,
            'ready_at' => now(),
            'state'    => WebpageStateEnum::READY,
        ];

        if ($website->state == WebsiteStateEnum::LIVE) {
            unset($webpageData['ready_at']);
            $webpageData['state']   = WebpageStateEnum::LIVE;
            $webpageData['live_at'] = now();
        }


        return $webpageData;
    }


    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("web.{$this->shop->website->id}.edit");
    }


    public function asController(Organisation $organisation, Website $website, ActionRequest $request): Website
    {
        $this->initialisationFromShop($website->shop, $request);

        return $this->handle($website);
    }

    public function getCommandSignature(): string
    {
        return 'website:seed-fixed-webpages {website?}';
    }

    public function asCommand(Command $command): int
    {
        if ($command->argument('website')) {
            try {
                /** @var Website $website */
                $website = Website::where('slug', $command->argument('website'))->firstOrFail();
                $this->handle($website);
                $command->info("$website->code fixed webpages seeded");
            } catch (Exception $e) {
                $command->error($e->getMessage());

                return 1;
            }
        } else {
            foreach (Website::all() as $website) {
                $this->handle($website);
                $command->info("$website->code fixed webpages seeded");
            }
        }


        return 0;
    }

}
