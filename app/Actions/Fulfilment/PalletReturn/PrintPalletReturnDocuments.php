<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 23 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Dispatching\DeliveryNoteLeaflet\WithPrintDeliveryNoteLeaflet;
use App\Actions\Fulfilment\PalletReturn\Pdf\PdfPalletReturnParcelDocuments;
use App\Actions\Fulfilment\PalletReturn\Pdf\PdfPalletReturnShippingLabel;
use App\Actions\Fulfilment\PalletReturnLeaflet\WithPalletReturnLeafletPrinting;
use App\Actions\Helpers\Media\MergePdfs;
use App\Actions\OrgAction;
use App\Actions\Traits\WithPrintNode;
use App\Models\Fulfilment\PalletReturn;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

class PrintPalletReturnDocuments extends OrgAction
{
    use WithPalletReturnLeafletPrinting;
    use WithPrintNode;
    use WithPrintDeliveryNoteLeaflet;

    /**
     * One click sends everything the packer needs to the printers: the shipping label and the
     * invoice, delivery note and product manuals to the user's printer, the inserts to the leaflet printer.
     */
    public function handle(PalletReturn $palletReturn, User $user): int
    {
        $shippingLabels = PdfPalletReturnShippingLabel::make()->documents($palletReturn);
        $parcelDocuments = PdfPalletReturnParcelDocuments::make()->documents($palletReturn);
        $leaflets = $palletReturn->leaflets()->printable()->get();

        if (!$shippingLabels && !$parcelDocuments && $leaflets->isEmpty()) {
            throw ValidationException::withMessages([
                'messages' => __('There is nothing to print: no shipping label, invoice, delivery note, manual or insert on this return.'),
            ]);
        }

        if (!Arr::get($user->group->settings, 'printnode.print_by_printnode', false)) {
            throw ValidationException::withMessages([
                'messages' => __('Print by Printnode is not enabled for your group!'),
            ]);
        }

        $documentPrinterId = Arr::get($user->settings, 'preferred_printer_id');
        $leafletPrinterId = $this->leafletPrinterId($user);

        if (($shippingLabels || $parcelDocuments) && !$documentPrinterId) {
            throw ValidationException::withMessages([
                'messages' => __('You must set a preferred printer in your user settings!'),
            ]);
        }

        if ($leaflets->isNotEmpty() && !$leafletPrinterId) {
            throw ValidationException::withMessages([
                'messages' => __('You must set a leaflet printer or a preferred printer in your user settings!'),
            ]);
        }

        $printed = 0;

        try {
            foreach ([__('Shipping label') => $shippingLabels, __('Documents') => $parcelDocuments] as $title => $documents) {
                if ($documents) {
                    $this->printPdf(
                        title: $title.' '.$palletReturn->reference,
                        printId: (int) $documentPrinterId,
                        pdfBase64: base64_encode(MergePdfs::run($documents))
                    );
                    $printed++;
                }
            }

            foreach ($leaflets as $leaflet) {
                if ($this->printDeliveryNoteLeaflet($leaflet, $leafletPrinterId)) {
                    $printed++;
                }
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error printing pallet return documents: '.$e->getMessage());
            throw ValidationException::withMessages([
                'messages' => __('Error printing the documents of this return'),
            ]);
        }

        return $printed;
    }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): int
    {
        $this->palletReturn = $palletReturn;
        $this->initialisationFromGroup($palletReturn->group, $request);

        return $this->handle($palletReturn, $request->user());
    }

    public function htmlResponse(int $printed): RedirectResponse
    {
        return Redirect::back()->with('notification', [
            'status'      => 'success',
            'title'       => __('Success!'),
            'description' => trans_choice('{0} Nothing was sent to the printer.|{1} :count print job sent to the printer.|[2,*] :count print jobs sent to the printers.', $printed, ['count' => $printed]),
        ]);
    }
}
