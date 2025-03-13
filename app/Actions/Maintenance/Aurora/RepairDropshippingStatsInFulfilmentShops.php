<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 13 Mar 2025 11:40:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */




/** @noinspection DuplicatedCode */

namespace App\Actions\Maintenance\Aurora;

use App\Actions\Traits\WithOrganisationSource;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\Shop;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class RepairDropshippingStatsInFulfilmentShops
{
    use AsAction;
    use WithOrganisationSource;

    private int $count = 0;

    /**
     * @throws \Throwable
     */
    public function handle(?Command $command): void
    {
        /** @var Shop $shop */
        foreach (Shop::where('type', ShopTypeEnum::FULFILMENT)->get() as $shop) {
            $shop->dropshippingStats()->create();
        }
    }




    public function getCommandSignature(): string
    {
        return 'maintenance:add_ds_stats_to_fulfilment_shops';
    }

    public function asCommand(Command $command): int
    {
        try {
            $this->handle($command);
        } catch (Throwable $e) {
            $command->error($e->getMessage());

            return 1;
        }

        return 0;
    }

}
