<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 22:30:00 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\DeliveryNoteLeaflet;

use App\Actions\Dispatching\DeliveryNote\WithDeliveryNoteWorkAuthorisation;
use App\Actions\OrgAction;
use App\Models\Dispatching\DeliveryNoteLeaflet;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\Response;

/**
 * The leaflet as an A6 PDF for the packer to print from the browser's own print dialog,
 * which prints on the paper set on that computer and needs no PrintNode.
 * POST because handing the file out marks the insert printed.
 */
class PrintDeliveryNoteLeafletInBrowser extends OrgAction
{
    use WithDeliveryNoteWorkAuthorisation;
    use WithPrintDeliveryNoteLeaflet;

    public function handle(DeliveryNoteLeaflet $deliveryNoteLeaflet): Response
    {
        $pdf = $this->leafletPdf($deliveryNoteLeaflet);

        if ($pdf === null) {
            throw ValidationException::withMessages([
                'messages' => __('This insert has no uploaded file to print.'),
            ]);
        }

        $this->markLeafletPrinted($deliveryNoteLeaflet);

        return response($pdf, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="insert-'.$deliveryNoteLeaflet->id.'.pdf"');
    }

    public function asController(DeliveryNoteLeaflet $deliveryNoteLeaflet, ActionRequest $request): Response
    {
        $this->initialisationFromGroup($deliveryNoteLeaflet->group, $request);

        return $this->handle($deliveryNoteLeaflet);
    }
}
