<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\Shipment\ApiCalls;

use App\Models\Dispatching\Event;
use App\Models\Dispatching\Shipment;
use App\Models\Dispatching\Shipper;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class ApcGbTrack
{
    use AsAction;
    use WithAttributes;
    public function handle(Shipment $shipment, Shipper $shipper): false|string
    {
        $headers =  [
            "remote-user: Basic ".base64_encode($shipper->email.':'.$shipper->data['password']),
            "Content-Type: application/json"
        ];
        if (!$shipment->tracking) {
            return false;
        }

        $apiResponse = ProsesApiCalls::run(
            'https://apc.hypaship.com/api/3.0/Tracks/'.$shipment->tracking.'.json?searchtype=CarrierWaybill&history=Yes',
            $headers,
            "[]",
            'GET'
        );


        $boxes = Arr::get($apiResponse, 'data.Tracks.Track.ShipmentDetails.Items.0.Item');

        if ($boxes != null) {
            if (array_keys($boxes) !== range(0, count($boxes) - 1)) {
                $this->track_box($shipment, $boxes);
            } else {
                foreach ($boxes as $box) {
                    $this->track_box($shipment, $box);
                }
            }

            $shipment->update_state();
        }

        return true;
    }

    private function track_box(Shipment $shipment, mixed $boxes): void
    {
        $boxID = $boxes['TrackingNumber'];
        if (array_keys($boxes['Activity']) !== range(0, count($boxes['Activity']) - 1)) {
            $this->save_event($boxes['Activity']['Status'], $boxID, $shipment);
        } else {
            foreach ($boxes['Activity'] as $eventData) {
                $this->save_event($eventData['Status'], $boxID, $shipment);
            }
        }
    }

    private function save_event($eventData, $boxID, Shipment $shipment)
    {
        try {
            $date = Carbon::createFromFormat('d/m/Y H:i:s', Arr::pull($eventData, 'DateTime'), 'Europe/London');
            $date->setTimezone('UTC');

        } catch (Exception) {
            return false;
        }


        $code   = Str::of(strtolower(Arr::get($eventData, 'StatusDescription')))->snake();
        $state  = null;
        $status = null;
        switch ($code) {

            case 'ready_to_print':
                $state = 100;
                break;
            case 'label_printed/_done':
                $code  = 'label_printed';
                $state = 100;
                break;
            case 'manifested':
            case 'at_hub':
            case 'at_depot':
            case 'scan':
            case 'not_received_in_depot':
                $state = 200;
                break;
            case 'at_delivery_depot':
            case 'at_sending_depot':
                $code  = 'at_delivery_depot';
                $state = 200;
                break;
            case 'problem-_not_attempted':
                $code  = 'problem_not_attempted';
                $state = 200;
                break;
            case 'out_for_delivery':
                $state = 300;
                break;
            case 'closed/_carded':
                $code  = 'closed_carded';
                $state = 400;
                break;
            case 'not_received_on_trunk':
            case 'held_at_depot':
            case 'check_address':
                $state = 400;
                break;
            case 'updated/resolved':
                $code  = 'updated_resolved';
                $state = 400;
                break;

            case 'delivered':
            case 'customer_refused':
            case 'left_with_neightbour':
            case 'collected_from_depot':
            case 'left_as_instructed':
            case 'return_to_sender':
                $state = 500;
                break;
            case 'cancelled':
                $state = 0;
                // no break
            default:

        }

        switch (Arr::pull($eventData, 'StatusColor')) {
            case 'green':
                $status = 300;
                break;
            case 'orange':
                $status = 200;
                break;
            case 'red':
                $status = 100;
                break;
        }


        $eventData = array_filter($eventData);

        $event = (new Event())->firstOrCreate(
            [
                'date'        => $date->format('Y-m-d H:i:s'),
                'box'         => $boxID,
                'code'        => $code,
                'shipment_id' => $shipment->id
            ],
            [
                'state'  => $state,
                'status' => $status,
                'data'   => $eventData
            ]
        );
        return $event->id;
    }
}
