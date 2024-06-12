<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jun 2024 22:15:20 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Mail\Outbox\OutboxStateEnum;
use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Mail\Outbox;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateOutboxes
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
            'number_outboxes' => $shop->outboxes()->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'outboxes',
                field: 'type',
                enum: OutboxTypeEnum::class,
                models: Outbox::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
                }
            )
        );


        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'outboxes',
                field: 'state',
                enum: OutboxStateEnum::class,
                models: Outbox::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
                }
            )
        );

        $shop->mailStats()->update($stats);
    }


}
