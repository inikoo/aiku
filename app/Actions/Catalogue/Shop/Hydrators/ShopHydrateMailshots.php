<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 24-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Comms\Mailshot\MailshotStateEnum;
use App\Enums\Comms\Mailshot\MailshotTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Comms\Mailshot;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateMailshots
{
    use AsAction;
    use WithEnumStats;

    private Shop $shop;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->shop->id))->dontRelease()];
    }

    public function handle(Shop $shop): void
    {
        $stats = [
            'number_mailshots' => $shop->mailshots()->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'mailshots',
                field: 'state',
                enum: MailshotStateEnum::class,
                models: Mailshot::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'mailshots',
                field: 'type',
                enum: MailshotTypeEnum::class,
                models: Mailshot::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
                }
            )
        );

        $shop->commsStats->update($stats);
    }

}
