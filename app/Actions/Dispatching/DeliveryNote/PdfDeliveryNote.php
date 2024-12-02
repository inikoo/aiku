<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 25-10-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Dispatching\DeliveryNote;

use App\Actions\Traits\WithExportData;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Symfony\Component\HttpFoundation\Response;

class PdfDeliveryNote
{
    use AsAction;
    use WithAttributes;
    use WithExportData;

    /**
     * @throws MpdfException
     */
    public function handle(DeliveryNote $deliveryNote): Response
    {
        // Retrieve delivery note details
        // $totalItemsNet = (float) $deliveryNote->total_amount;
        // $totalShipping = (float) $deliveryNote->order?->shipping_amount ?? 0;
        // $totalNet = $totalItemsNet + $totalShipping;

        // Prepare data to pass to the Blade template
        $filename = $deliveryNote->slug . '-' . Carbon::now()->format('Y-m-d');

        // Generate PDF using Blade template and data array
        $pdf = PDF::loadView('deliveryNote.templates.pdf.delivery-note', [
            'deliverynote' => $deliveryNote,
            'order'        => $deliveryNote->orders->first(),
            'customer'     => $deliveryNote->customer,
            'deliveryAddress' => $deliveryNote->deliveryAddress->formatted_address,
            'items'        => $deliveryNote->deliveryNoteItems,
        ]);

        return $pdf->stream($filename . '.pdf');
    }

    /**
     * @throws MpdfException
     */
    public function asController(Organisation $organisation, Warehouse $warehouse, DeliveryNote $deliveryNote): Response
    {
        return $this->handle($deliveryNote);
    }
}
