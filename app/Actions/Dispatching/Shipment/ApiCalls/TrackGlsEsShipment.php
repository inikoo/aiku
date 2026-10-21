<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Oct 2026 15:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\Shipment\ApiCalls;

use App\Actions\Dropshipping\Shopify\Fulfilment\MarkShopifyFulfilmentDelivered;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\Ordering\Platform\PlatformTypeEnum;
use App\Models\Dispatching\Shipment;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;
use SimpleXMLElement;

/**
 * Asks GLS Spain where each parcel of the shipment is (GetExpCli, the same client uid that prints
 * the labels). The shipment counts as delivered once every parcel is, at the time the last one was.
 */
class TrackGlsEsShipment
{
    use AsAction;

    public const string DELIVERED = '7';

    public function handle(Shipment $shipment): Shipment
    {
        $gls         = CallApiGlsEsShipping::make();
        $uid         = $gls->getAccessToken($shipment->shipper);
        $codes       = array_values(array_filter($shipment->trackings ?: [$shipment->tracking]));
        $statuses    = [];
        $deliveredAt = [];

        foreach ($codes as $code) {
            $expedition = $this->getExpedition($gls->getBaseUrl(), (string)$code, $uid);
            if (!$expedition && count($codes) == 1 && Arr::get($shipment->data, 'codbarras')) {
                $expedition = $this->getExpedition($gls->getBaseUrl(), Arr::get($shipment->data, 'codbarras'), $uid);
            }
            if (!$expedition) {
                continue;
            }

            $statuses[] = trim((string)$expedition->estado);
            if (trim((string)$expedition->codestado) === self::DELIVERED) {
                $pod           = rescue(fn () => Carbon::createFromFormat('d/m/Y H:i:s', trim((string)$expedition->pod), 'Europe/Madrid'), null, false);
                $deliveredAt[] = $pod ? $pod->setTimezone(config('app.timezone')) : now();
            }
        }

        $shipment->tracked_at = now();
        if ($statuses) {
            $shipment->carrier_status = implode(', ', array_unique($statuses));
        }
        if (!$shipment->delivered_at && $codes && count($deliveredAt) == count($codes)) {
            $shipment->delivered_at = max($deliveredAt);
        }

        $isNewlyDelivered = $shipment->isDirty('delivered_at');
        $shipment->save();

        if ($isNewlyDelivered) {
            $this->tellPlatforms($shipment, $shipment->delivered_at);
        }

        return $shipment;
    }

    private function getExpedition(string $baseUrl, string $code, string $uid): ?SimpleXMLElement
    {
        $response = Http::asForm()->timeout(20)->post($baseUrl.'/b2b.asmx/GetExpCli', [
            'codigo'  => $code,
            'uid'     => $uid,
            'TipoUTC' => '0',
        ]);

        if (!$response->successful()) {
            return null;
        }

        $xml = @simplexml_load_string($response->body());

        return $xml && isset($xml->exp[0]) ? $xml->exp[0] : null;
    }

    private function tellPlatforms(Shipment $shipment, CarbonInterface $deliveredAt): void
    {
        foreach ($shipment->deliveryNotes as $deliveryNote) {
            if ($deliveryNote->shipments()->whereNull('delivered_at')->exists()) {
                continue;
            }

            foreach ($deliveryNote->orders as $order) {
                if ($order->shop->type == ShopTypeEnum::DROPSHIPPING
                    && $order->customerSalesChannel?->platform?->type == PlatformTypeEnum::SHOPIFY) {
                    MarkShopifyFulfilmentDelivered::dispatch($order, $deliveredAt);
                }
            }
        }
    }
}
