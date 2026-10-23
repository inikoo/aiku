<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 25 Feb 2026 22:10:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop\External\Faire;

use App\Actions\OrgAction;
use App\Models\Catalogue\Shop;
use App\Models\Ordering\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class AcceptFaireOrder extends OrgAction
{
    public $commandSignature = 'faire:order_accepted {order}';

    public function handle(Order $order, ?string $shipAfter = null): void
    {
        $shop = $order->shop;
        if (app()->isProduction()) {
            $shop->acceptFaireOrder(
                $order->external_id,
                [
                    'expected_ship_date' => $this->getExpectedShipDate($shop, $shipAfter)->toIso8601String()
                ]
            );

            DownloadFairePackingPdfSlip::dispatch($order->id)->delay(now()->addHour());
        }
    }

    /**
     * A backorder cannot ship before the date the brand promised the retailer on Faire.
     */
    public function getExpectedShipDate(Shop $shop, ?string $shipAfter): Carbon
    {
        $expectedShipDate = Carbon::now()->addDays(Arr::get($shop->settings, 'faire.order_from_days', 6));

        if ($shipAfter) {
            return $expectedShipDate->max(Carbon::parse($shipAfter));
        }

        return $expectedShipDate;
    }

    public function asCommand(Command $command): void
    {
        $order = Order::where('slug', $command->argument('order'))->firstOrFail();
        $this->handle($order);
    }
}
