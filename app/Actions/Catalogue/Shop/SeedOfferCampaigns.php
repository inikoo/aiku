<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 10 Sept 2024 14:55:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop;

use App\Actions\Discounts\OfferCampaign\StoreOfferCampaign;
use App\Actions\GrpAction;
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
        foreach (OfferCampaignTypeEnum::cases() as $case) {
            $code = $case->value;

            if ($shop->offerCampaigns()->where('code', $code)->exists()) {
                continue;
            }


            StoreOfferCampaign::make()->action(
                $shop,
                [
                    'code' => $case->codes()[$case->value],
                    'name' => $case->labels()[$case->value],
                    'type' => $case
                ]
            );
        }
    }

    public string $commandSignature = 'shop:seed-offer-campaigns {shop : shop slug}';

    public function asCommand(Command $command): int
    {
        try {
            $shop = Shop::where('slug', $command->argument('shop'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $this->handle($shop);
        echo "Success seed the offer campaigns ✅ \n";

        return 0;
    }
}
