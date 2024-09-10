<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 10 Sept 2024 14:55:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop;

use App\Actions\Discounts\OfferCampaign\StoreOfferCampaign;
use App\Actions\GrpAction;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\Discounts\OfferCampaign\OfferCampaignTypeEnum;
use App\Models\Catalogue\Shop;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedOfferCampaigns extends GrpAction
{
    use AsAction;

    public function handle(Shop $shop): void
    {

        if($shop->type!=ShopTypeEnum::B2B) {
            return;
        }

        foreach (OfferCampaignTypeEnum::cases() as $case) {
            $code =$case->codes()[$case->value];

            if ($shop->offerCampaigns()->where('code', $code)->exists()) {
                continue;
            }

            StoreOfferCampaign::make()->action(
                $shop,
                [
                    'code' => $code,
                    'name' => $case->labels()[$case->value],
                    'type' => $case
                ]
            );
        }
    }

    public string $commandSignature = 'shop:seed-offer-campaigns {shop? : shop slug}';

    public function asCommand(Command $command): int
    {
        if($command->argument('shop')) {
            try {
                $shop = Shop::where('slug', $command->argument('shop'))->firstOrFail();
            } catch (Exception $e) {
                $command->error($e->getMessage());

                return 1;
            }
            $this->handle($shop);
            echo "Success seed shop offer campaigns ✅ \n";
        } else {
            foreach (Shop::all() as $shop) {
                $this->handle($shop);
            }
            echo "Success seed all shops offer campaigns ✅ \n";
        }




        return 0;
    }
}
