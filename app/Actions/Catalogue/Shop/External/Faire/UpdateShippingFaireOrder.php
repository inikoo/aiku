<?php

namespace App\Actions\Catalogue\Shop\External\Faire;

use App\Actions\OrgAction;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Ordering\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Sentry;

class UpdateShippingFaireOrder extends OrgAction
{
    public function handle(DeliveryNote $deliveryNote, Command|null $command = null): array
    {

        if (!app()->isProduction()) {
            return [
                'status' => 'success',
                'msg'    => __('Order not updated in local environment')
            ];
        }

        $order = $deliveryNote->orders()->first();
        if (
            $order && $order->shop->type == ShopTypeEnum::EXTERNAL && $order->external_id && !$order->is_shipping_by_external
            && app()->isProduction()
        ) {
            try {
                $shipments = $this->getFaireShipments($deliveryNote, $order);

                $result = $order->shop->updateShippingFaireOrder($order->external_id, [
                    'shipments' => $shipments,
                ]);


                $hasErr = Arr::has($result, 'error');

                if (!Arr::get($result, 'success') && $hasErr) {
                    Sentry::captureMessage(json_encode(Arr::get($result, 'error.message', [])));
                    return [
                        'status' => 'fail',
                        'msg'    => Arr::get($result, 'error.message')
                    ];
                } else {
                    try {
                        UpdateFaireOrder::make()->syncFaireTax($order);
                    } catch (\Throwable $e) {
                        Sentry::captureException($e);
                    }

                    return [
                        'status' => 'success',
                        'msg'    => __('Faire order updated successfully')
                    ];
                }
            } catch (\Exception $e) {
                $command?->error('Order '.$order->external_id.' not updated '.$e->getMessage());
                Sentry::captureException($e);

                return [
                    'status' => 'fail',
                    'msg'    => $e->getMessage()
                ];
            }
        } else {
            return [
                'status' => 'fail',
                'msg'    => __('Unprocessable order')
            ];
        }
    }

    /**
     * Faire reimburses the maker_cost sent with the tracking. A shipment rarely carries its own
     * cost, so the shipping staff typed on the order is sent instead (HELP-3906): that is what
     * saves them entering it again on Faire. It all goes on the first tracking, never spread:
     * every new shipment re-sends the whole list, and a share that moved between trackings could
     * be counted twice. A label Faire itself issued is never given a cost, Faire pays none on it.
     *
     * @return array<int, array{carrier: string, tracking_code: string, maker_cost: array{amount_minor: int, currency: string}}>
     */
    public function getFaireShipments(DeliveryNote $deliveryNote, Order $order): array
    {
        $shipments = [];

        $hasFaireLabel = false;

        foreach ($deliveryNote->shipments->sortBy('id') as $shipment) {
            $hasFaireLabel = $hasFaireLabel || str_ends_with((string)$shipment->shipper->code, '-faire');
            $trackings     = preg_split('/[,\/]+/', (string)$shipment->tracking, -1, PREG_SPLIT_NO_EMPTY);
            if (!$trackings) {
                continue;
            }

            $cost = (int)((int)round($shipment->cost * 100) / count($trackings));

            foreach ($trackings as $tracking) {
                $shipments[] = [
                    'carrier'       => $shipment->shipper->trade_as,
                    'tracking_code' => trim($tracking),
                    'maker_cost'    => [
                        'amount_minor' => $cost,
                        'currency'     => $order->currency->code,
                    ],
                ];
            }
        }

        if ($shipments && !$hasFaireLabel && array_sum(array_column(array_column($shipments, 'maker_cost'), 'amount_minor')) == 0) {
            $shipments[0]['maker_cost']['amount_minor'] = max(0, (int)round($order->shipping_amount * 100));
        }

        return $shipments;
    }

    public function getCommandSignature(): string
    {
        return 'shop:update_shipping_faire_order {delivery_note}';
    }

    public function asCommand(Command $command): void
    {
        $deliveryNote = DeliveryNote::where('slug', $command->argument('delivery_note'))->firstOrFail();
        $result       = $this->handle($deliveryNote, $command);
        if ($result['status'] == 'fail') {
            $command->error($result['msg']);
        } else {
            $command->info($result['msg']);
        }
    }
}
