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
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class PrintPalletReturnLeaflets extends OrgAction
{
    use WithPalletReturnLeafletPrinting;
    use WithPrintNode;
    use WithPrintDeliveryNoteLeaflet;

    public function handle(PalletReturn $palletReturn): RedirectResponse
    {
        $printed = 0;

        try {
            foreach ($palletReturn->leaflets()->printable()->get() as $leaflet) {
                if ($this->printDeliveryNoteLeaflet($leaflet, $this->get('printerId'))) {
                    $printed++;
                }
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error printing pallet return leaflets: '.$e->getMessage());
            throw ValidationException::withMessages([
                'messages' => __('Error printing leaflets'),
            ]);
        }

        return Redirect::back()->with('notification', [
            'status'      => 'success',
            'title'       => __('Success!'),
            'description' => trans_choice('{0} No inserts to print.|{1} :count insert sent to printer.|[2,*] :count inserts sent to printer.', $printed, ['count' => $printed]),
        ]);
    }

    public function afterValidator(Validator $validator): void
    {
        $this->setPrintNodePrinter();
    }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): RedirectResponse
    {
        $this->palletReturn = $palletReturn;
        $this->initialisationFromGroup($palletReturn->group, $request);

        return $this->handle($palletReturn);
    }
}
