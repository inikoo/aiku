<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn\Pdf;

use App\Actions\Traits\Authorisations\WithPalletDocumentAuthorisation;
use App\Enums\Dispatching\Shipment\ShipmentLabelTypeEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnAttachmentScopeEnum;
use App\Models\Dispatching\Shipment;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\Response;

class PdfPalletReturnShippingLabel
{
    use WithPalletDocumentAuthorisation;
    use AsAction;
    use WithPalletReturnPrintDocuments;

    public function handle(PalletReturn $palletReturn): Response
    {
        return $this->mergedPdfResponse(
            $this->documents($palletReturn),
            'shipping-label-'.$palletReturn->slug.'.pdf',
            __('There is no shipping label yet: the customer has not attached one and no shipment with a label has been created.')
        );
    }

    /**
     * The label the customer attached wins; without one, the labels of the shipments staff created.
     *
     * @return array<int, array{name: string, pdf: string}>
     */
    public function documents(PalletReturn $palletReturn): array
    {
        $documents = $this->attachmentDocuments($palletReturn, [PalletReturnAttachmentScopeEnum::SHIPPING_LABEL->value]);

        if ($documents) {
            return $documents;
        }

        return $palletReturn->shipments()
            ->get()
            ->map(fn (Shipment $shipment) => $this->shipmentLabel($shipment))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{name: string, pdf: string}|null
     */
    private function shipmentLabel(Shipment $shipment): ?array
    {
        $name = $shipment->tracking ?: $shipment->reference ?: (string) $shipment->id;

        if ($shipment->label && $shipment->label_type === ShipmentLabelTypeEnum::PDF) {
            return ['name' => $name, 'pdf' => base64_decode($shipment->label)];
        }

        if ($shipment->label && $shipment->label_type === ShipmentLabelTypeEnum::HTML) {
            return [
                'name' => $name,
                'pdf'  => Browsershot::html(base64_decode($shipment->label))
                    ->setOption('no-stop-slow-scripts', true)
                    ->setOption('timeout', 5000)
                    ->margins(10, 10, 10, 10)
                    ->pdf(),
            ];
        }

        if ($shipment->combined_label_url) {
            $response = Http::timeout(30)->get($shipment->combined_label_url);

            return $response->successful() ? ['name' => $name, 'pdf' => $response->body()] : null;
        }

        return null;
    }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): Response
    {
        return $this->handle($palletReturn);
    }
}
