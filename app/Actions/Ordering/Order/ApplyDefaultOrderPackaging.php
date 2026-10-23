<?php

/*
 * Author: Andi Ferdiawan
 * Created: Fri, 10 Jul 2026 23:30:00 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2026, Inikoo Ltd
 */

namespace App\Actions\Ordering\Order;

use App\Enums\Catalogue\Leaflet\LeafletStateEnum;
use App\Enums\Catalogue\Packaging\PackagingStateEnum;
use App\Models\Billables\ModelHasLeaflet;
use App\Models\CRM\CustomerHasPackaging;
use App\Models\Ordering\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class ApplyDefaultOrderPackaging
{
    use AsAction;

    public function handle(Order $order): Order
    {
        if (!$order->shop?->hasPackagingAndInserts()) {
            return $order;
        }

        if ($order->insert_types !== null) {
            return $order;
        }

        $customer = $order->customer;
        if (!$customer) {
            return $order;
        }

        $shop = $order->shop;

        $preferredPackaging = CustomerHasPackaging::where('customer_id', $customer->id)
            ->whereHas('packaging', fn ($query) => $query->where('shop_id', $shop->id)->where('state', PackagingStateEnum::ACTIVE))
            ->with('packaging')
            ->get()
            ->sortBy(fn (CustomerHasPackaging $row) => (float) $row->packaging?->price)
            ->first()
            ?->packaging;

        $preferredPackaging = $preferredPackaging ?? $shop->defaultPackaging();

        if (!$preferredPackaging) {
            return $order;
        }

        $packagingData = [
            'packaging_id' => $preferredPackaging->id,
            'leaflet_ids'  => $this->getDefaultLeafletIds($shop->id, $customer->id, $preferredPackaging->family_code),
        ];

        $defaultPersonalisedMessage = $this->getDefaultPersonalisedMessage($shop->id, $customer->id);
        if (blank($order->personalised_message) && filled($defaultPersonalisedMessage)) {
            $packagingData['personalised_message'] = $defaultPersonalisedMessage;
        }

        UpdateOrderPackaging::make()->action($order, $packagingData);

        return $order;
    }

    private function getDefaultPersonalisedMessage(int $shopId, int $customerId): ?string
    {
        return CustomerHasPackaging::where('customer_id', $customerId)
            ->whereHas('packaging', fn ($query) => $query->where('shop_id', $shopId))
            ->whereNotNull('personalised_message')
            ->value('personalised_message');
    }

    /** @return array<int, int> */
    private function getDefaultLeafletIds(int $shopId, int $customerId, ?string $familyCode): array
    {
        if (!$familyCode) {
            return [];
        }

        return ModelHasLeaflet::where('model_type', 'Customer')
            ->where('model_id', $customerId)
            ->where('shop_id', $shopId)
            ->where('state', LeafletStateEnum::ACTIVE)
            ->whereHas('packaging', fn ($query) => $query->where('family_code', $familyCode))
            ->pluck('leaflet_id')
            ->unique()
            ->values()
            ->all();
    }
}
