<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 20 Jul 2023 09:57:45 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\Hydrators;

use App\Actions\HydrateModel;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithEnumStats;
use App\Enums\Fulfilment\StoredItem\StoredItemStateEnum;
use App\Models\Fulfilment\Pallet;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class PalletHydrateStoredItems extends HydrateModel
{
    use WithActionUpdate;
    use WithEnumStats;

    private Pallet $pallet;

    public function __construct(Pallet $pallet)
    {
        $this->pallet = $pallet;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->pallet->id))->dontRelease()];
    }

    public function handle(Pallet $pallet): void
    {
        $numberStoredItems = $pallet->storedItems()->count();
        $stats             = [
            'number_stored_items'                     => $numberStoredItems,
            'with_stored_items'                       => $numberStoredItems > 0,
            'number_stored_items_state_in_process'    => $pallet->storedItems()
                ->where('stored_items.state', StoredItemStateEnum::IN_PROCESS)->count(),
            'number_stored_items_state_submitted'    => $pallet->storedItems()
                ->where('stored_items.state', StoredItemStateEnum::SUBMITTED)->count(),
            'number_stored_items_state_discontinuing' => $pallet->storedItems()
                ->where('stored_items.state', StoredItemStateEnum::DISCONTINUING)->count(),
            'number_stored_items_state_active'        => $pallet->storedItems()
                ->where('stored_items.state', StoredItemStateEnum::ACTIVE)->count(),
        ];
        $pallet->update($stats);
    }
}
