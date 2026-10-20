<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 17 Sep 2026 12:00:00 Central European Summer Time, Trnava, Slovakia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\CustomerSalesChannel\Json;

use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Dropshipping\CustomerSalesChannel;
use Lorisleiva\Actions\ActionRequest;

trait WithRetinaOwnedCustomerSalesChannel
{
    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction || str_starts_with((string) $request->route()?->getName(), 'retina.')) {
            return true;
        }

        $customerSalesChannel = $request->route('customerSalesChannel');
        if (!$customerSalesChannel instanceof CustomerSalesChannel || !$customerSalesChannel->exists) {
            return false;
        }

        $shop = $customerSalesChannel->shop;
        if ($shop->type == ShopTypeEnum::FULFILMENT) {
            return $request->user()->authTo([
                "fulfilment-shop.{$shop->fulfilment->id}.view",
                "supervisor-fulfilment-shop.{$shop->fulfilment->id}",
                "accounting.$shop->organisation_id.view",
            ]);
        }

        return $request->user()->authTo([
            "crm.$shop->id.view",
            "accounting.$shop->organisation_id.view",
        ]);
    }

    public function inRetina(CustomerSalesChannel $customerSalesChannel, ActionRequest $request): ?array
    {
        abort_unless($customerSalesChannel->customer_id === $request->user()?->customer_id, 403);

        return $this->asController($customerSalesChannel, $request);
    }
}
