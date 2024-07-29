<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\Shipment\ApiCalls;

use App\Models\Dispatching\DeliveryNote;
use App\Models\Dispatching\PdfLabel;
use App\Models\Dispatching\Shipper;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use SoapClient;

class PostmenCallShipperApi
{
    use AsAction;
    use WithAttributes;
    public function handle(DeliveryNote $shipment, Request $request, Shipper $shipper): array
    {
        $debug              = Arr::get($shipper->data, 'debug') == 'Yes';
        $apiUrl             = "https://api.mygls.sk/ParcelService.svc?singleWsdl";
        $shipmentParams     = DpdSkShipmentParameters::run($request, $shipper);
        $printLabelsRequest = array(
            'Username'   => $shipper->data['username'],
            'Password'   => hex2bin($shipper->data['password']),
            'ParcelList' => $shipmentParams
        );
        $shipment->data['boxes'] = $shipmentParams[0]->Count;
        if ($debug) {
            $shipmentData = $shipment->data;
            data_fill($shipmentData, 'debug.request', json_decode(json_encode($printLabelsRequest['ParcelList']), true));
            $shipment->data = $shipmentData;
        }
        $shipment->save();
        $printLabelsRequest = array("printLabelsRequest" => $printLabelsRequest);

        $soapOptions = array(
            'soap_version'   => SOAP_1_1,
            'stream_context' => stream_context_create(array('ssl' => array('cafile' => '../assets/ca_cert.pem')))
        );


        try {
            $client = new SoapClient($this->api_url, $soapOptions);
        } catch (\SoapFault $e) {
            $result['errors'] = ['Soap API connection error'];
            return $result;
        }
        $apiResponse = $client->PrintLabels($printLabelsRequest)->PrintLabelsResult;


        if ($debug) {
            $shipmentData = $shipment->data;
            data_fill($shipmentData, 'debug.response', json_decode(json_encode($apiResponse), true));
            $shipment->data = $shipmentData;
        }
        $shipment->status = 'error';


        $result = [
            'shipment_id' => $shipment->id
        ];

        if (count((array)$apiResponse->PrintLabelsErrorList)) {

            $result['errors'] = [$apiResponse->PrintLabelsErrorList];
            $result['status'] = 599;


            $msg                     = $apiResponse->PrintLabelsErrorList->ErrorInfo->ErrorDescription;
            $result['error_message'] = $msg;
            $shipment->error_message = $msg;


        } elseif ($apiResponse->Labels != "") {

            $pdfData     = $apiResponse->Labels;
            $pdfChecksum = md5($pdfData);
            $pdfLabel    = new PdfLabel(
                [
                    'checksum' => $pdfChecksum,
                    'data'     => base64_encode($pdfData)
                ]
            );
            $shipment->pdfLabel()->save($pdfLabel);

            $tracking_number = $apiResponse->PrintLabelsInfoList->PrintLabelsInfo->ParcelNumber;

            $shipment->status    = 'success';
            $shipment->tracking  = $tracking_number;

            $result['tracking_number'] = $tracking_number;
            $result['shipment_id']     = $shipment->id;
            // todo: create a public url for this
            $result['label_link']      = config('app.domain').'/labels/'.$pdfChecksum;

            $error_shipments = json_decode($request->get('error_shipments', '[]'));
            if (is_array($error_shipments) and count($error_shipments) > 0) {
                (new Shipment())->wherein('id', $error_shipments)->update(['status' => 'fixed']);
            }


        }

        $shipment->save();
        return $result;
    }
}
