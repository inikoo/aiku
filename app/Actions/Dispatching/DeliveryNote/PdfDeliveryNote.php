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
    public function handle(DeliveryNote $deliverynote): Response
    {
        // Retrieve delivery note details
        $totalItemsNet = (float) $deliverynote->total_amount;
        $totalShipping = (float) $deliverynote->order?->shipping_amount ?? 0;
        $totalNet = $totalItemsNet + $totalShipping;

        // Prepare data to pass to the Blade template
        $filename = $deliverynote->slug . '-' . Carbon::now()->format('Y-m-d');

        // Generate PDF using Blade template and data array
        $pdf = PDF::loadView('delivaryNote.templates.pdf.delivery-note', [
            'deliverynote' => $deliverynote,
            'order'        => $deliverynote->orders->first(),
            'customer'     => $deliverynote->customer,
            'deliveryAddress' => $deliverynote->deliveryAddress,
            'items'        => $deliverynote->deliveryNoteItems,
        ]);

        return $pdf->stream($filename . '.pdf');
    }

    /**
     * @throws MpdfException
     */
    public function asController(Organisation $organisation, Warehouse $warehouse, DeliveryNote $deliverynote): Response
    {
        return $this->handle($deliverynote);
    }
}
