<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Feb 2025 23:36:11 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletStoredItem;

use App\Actions\Fulfilment\PalletStoredItem\Hydrators\PalletStoreItemHydrateAudits;
use App\Actions\Traits\Hydrators\WithHydrateCommand;
use App\Models\Fulfilment\PalletStoredItem;

class HydratePalletStoreItem
{
    use WithHydrateCommand;
    public string $commandSignature = 'hydrate:pallet_stored_items {organisations?*} {--S|shop= shop slug} {--s|slug=}';

    public function __construct()
    {
        $this->model = PalletStoredItem::class;
    }

    public function handle(PalletStoredItem $palletStoredItem): void
    {
        PalletStoreItemHydrateAudits::run($palletStoredItem);

    }



}
