<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 15 Sept 2024 21:29:12 Malaysia Time, Taipei, Taiwan
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Actions\Traits\Hydrators\WithHydrateDeliveryNotes;
use App\Actions\Traits\WithEnumStats;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStatusEnum;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Dispatching\DeliveryNote;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateDeliveryNotes
{
    use AsAction;
    use WithEnumStats;
    use WithHydrateDeliveryNotes;

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
        $stats =$this->getDeliveryNotesStats($shop);

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'delivery_notes',
                field: 'type',
                enum: DeliveryNoteTypeEnum::class,
                models: DeliveryNote::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'delivery_notes',
                field: 'state',
                enum: DeliveryNoteStateEnum::class,
                models: DeliveryNote::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'delivery_notes',
                field: 'status',
                enum: DeliveryNoteStatusEnum::class,
                models: DeliveryNote::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
                }
            )
        );


        $shop->salesStats()->update($stats);
    }

}
