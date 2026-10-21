<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturnLeaflet;

use App\Actions\Dispatching\DeliveryNoteLeaflet\WithPrintDeliveryNoteLeaflet;
use App\Actions\OrgAction;
use App\Actions\Traits\WithPrintNode;
use App\Models\Fulfilment\PalletReturnLeaflet;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\Response;

class PrintPalletReturnLeafletInBrowser extends OrgAction
{
    use WithPalletReturnLeafletPrinting;
    use WithPrintNode;
    use WithPrintDeliveryNoteLeaflet;

    public function handle(PalletReturnLeaflet $palletReturnLeaflet): Response
    {
        $pdf = $this->leafletPdf($palletReturnLeaflet);

        if ($pdf === null) {
            throw ValidationException::withMessages([
                'messages' => __('This insert has no uploaded file to print.'),
            ]);
        }

        $this->markLeafletPrinted($palletReturnLeaflet);

        return response($pdf, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="insert-'.$palletReturnLeaflet->id.'.pdf"');
    }

    public function asController(PalletReturnLeaflet $palletReturnLeaflet, ActionRequest $request): Response
    {
        $this->palletReturn = $palletReturnLeaflet->palletReturn;
        $this->initialisationFromGroup($palletReturnLeaflet->group, $request);

        return $this->handle($palletReturnLeaflet);
    }
}
