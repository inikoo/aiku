<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Feb 2025 23:36:11 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\Fulfilment\StoredItem\Hydrators\StoreItemHydrateAudits;
use App\Actions\Fulfilment\StoredItem\Hydrators\StoreItemHydratePallets;
use App\Actions\Traits\Hydrators\WithHydrateCommand;
use App\Models\Fulfilment\StoredItem;

class HydrateStoreItem
{
    use WithHydrateCommand;

    public string $commandSignature = 'hydrate:stored_items {organisations?*} {--S|shop= shop slug} {--s|slug=}';

    public function __construct()
    {
        $this->model = StoredItem::class;
    }

    public function handle(StoredItem $storedItem): void
    {
        StoreItemHydratePallets::run($storedItem);
        StoreItemHydrateAudits::run($storedItem);
    }


}
