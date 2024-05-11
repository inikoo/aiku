<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:34:34 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\CRM\Prospect\ProspectContactedStateEnum;
use App\Enums\CRM\Prospect\ProspectFailStatusEnum;
use App\Enums\CRM\Prospect\ProspectStateEnum;
use App\Enums\CRM\Prospect\ProspectSuccessStatusEnum;
use App\Events\BroadcastProspectsDashboard;

use App\Models\CRM\Prospect;
use App\Models\Catalogue\Shop;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateProspects
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
            'number_prospects'                 => $shop->prospects()->where('shop_id', $shop->id)->count(),
            'number_prospects_dont_contact_me' => $shop->prospects()->where('shop_id', $shop->id)->where('dont_contact_me', true)->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'prospects',
                field: 'state',
                enum: ProspectStateEnum::class,
                models: Prospect::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'prospects',
                field: 'contacted_state',
                enum: ProspectContactedStateEnum::class,
                models: Prospect::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'prospects',
                field: 'fail_status',
                enum: ProspectFailStatusEnum::class,
                models: Prospect::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'prospects',
                field: 'success_status',
                enum: ProspectSuccessStatusEnum::class,
                models: Prospect::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
                }
            )
        );

        $crmStats = $shop->crmStats;
        $crmStats->update($stats);
        BroadcastProspectsDashboard::dispatch($crmStats->getChanges());

    }
}
