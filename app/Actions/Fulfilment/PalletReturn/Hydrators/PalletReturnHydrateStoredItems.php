<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 20 Jul 2023 09:57:45 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn\Hydrators;

use App\Actions\HydrateModel;
use App\Actions\Traits\WithEnumStats;
use App\Enums\Fulfilment\StoredItem\StoredItemStateEnum;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class PalletReturnHydrateStoredItems extends HydrateModel
{
    use AsAction;
    use WithEnumStats;

    private PalletReturn $palletReturn;
    public function __construct(PalletReturn $palletReturn)
    {
        $this->palletReturn = $palletReturn;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->palletReturn->id))->dontRelease()];
    }

    public function handle(PalletReturn $palletReturn): void
    {
        $stats = [
            'number_stored_items'                   => $palletReturn->storedItems()->count(),
            'number_stored_items_state_in_process'    => $palletReturn->storedItems()
                ->where('stored_items.state', StoredItemStateEnum::IN_PROCESS)->count(),
            'number_stored_items_state_submitted'    => $palletReturn->storedItems()
                ->where('stored_items.state', StoredItemStateEnum::SUBMITTED)->count(),
            'number_stored_items_state_discontinuing' => $palletReturn->storedItems()
                ->where('stored_items.state', StoredItemStateEnum::DISCONTINUING)->count(),
            'number_stored_items_state_active'        => $palletReturn->storedItems()
                ->where('stored_items.state', StoredItemStateEnum::ACTIVE)->count(),
            'number_stored_items_state_discontinued'  => $palletReturn->storedItems()
                ->where('stored_items.state', StoredItemStateEnum::DISCONTINUED)->count(),
        ];

        // $stats = array_merge($stats, $this->getEnumStats(
        //     model: 'stored_items',
        //     field: 'stored_items.state',
        //     enum: StoredItemStateEnum::class,
        //     models: StoredItem::class,
        //     where: function ($q) use ($palletReturn) {
        //         $q->join('pallet_return_items', 'stored_items.id', '=', 'pallet_return_items.stored_item_id')
        //         ->where('pallet_return_items.pallet_return_id', $palletReturn->id);
        //     }
        // ));

        $palletReturn->stats()->update($stats);
    }
}
