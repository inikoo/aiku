<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 24 Apr 2025 20:02:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Hydrators;

use App\Enums\Ordering\Platform\PlatformTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Dropshipping\Platform;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;

trait WithHydratePortfolios
{
    public function getPortfoliosStats(Group|Organisation|Shop $model): array
    {
        $shopifyPlatform = Platform::where('type', PlatformTypeEnum::SHOPIFY)->first();
        $wooCommercePlatform = Platform::where('type', PlatformTypeEnum::WOOCOMMERCE)->first();

        $counts = $model->portfolios()->toBase()
            ->selectRaw(
                'count(*) as total, count(*) filter (where status = true) as current, count(*) filter (where platform_id = ?) as shopify, count(*) filter (where platform_id = ?) as woocommerce',
                [$shopifyPlatform->id, $wooCommercePlatform->id]
            )
            ->first();

        return [
            'number_portfolios'                      => (int) $counts->total,
            'number_current_portfolios'              => (int) $counts->current,
            'number_portfolios_platform_shopify'     => (int) $counts->shopify,
            'number_portfolios_platform_woocommerce' => (int) $counts->woocommerce,
        ];
    }
}
