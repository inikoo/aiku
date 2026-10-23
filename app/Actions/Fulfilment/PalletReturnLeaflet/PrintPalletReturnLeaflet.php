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
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class PrintPalletReturnLeaflet extends OrgAction
{
    use WithPalletReturnLeafletPrinting;
    use WithPrintNode;
    use WithPrintDeliveryNoteLeaflet;

    public function handle(PalletReturnLeaflet $palletReturnLeaflet): RedirectResponse
    {
        if (!$palletReturnLeaflet->isPrintable()) {
            throw ValidationException::withMessages([
                'messages' => __('This insert has no uploaded file to print.'),
            ]);
        }

        try {
            $this->printDeliveryNoteLeaflet($palletReturnLeaflet, $this->get('printerId'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error printing pallet return leaflet: '.$e->getMessage());
            throw ValidationException::withMessages([
                'messages' => __('Error printing leaflet'),
            ]);
        }

        return Redirect::back()->with('notification', [
            'status'      => 'success',
            'title'       => __('Success!'),
            'description' => __('Insert sent to printer.'),
        ]);
    }

    public function afterValidator(Validator $validator): void
    {
        $this->setPrintNodePrinter();
    }

    public function asController(PalletReturnLeaflet $palletReturnLeaflet, ActionRequest $request): RedirectResponse
    {
        $this->palletReturn = $palletReturnLeaflet->palletReturn;
        $this->initialisationFromGroup($palletReturnLeaflet->group, $request);

        return $this->handle($palletReturnLeaflet);
    }
}
