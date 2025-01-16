<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Actions\Traits\WithExportData;
use App\Models\Accounting\Invoice;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Symfony\Component\HttpFoundation\Response;

class PdfInvoice
{
    use AsAction;
    use WithAttributes;
    use WithExportData;

    /**
     * @throws \Mpdf\MpdfException
     */
    public function handle(Invoice $invoice): Response
    {
        try {
            $totalItemsNet = (int) $invoice->total_amount;
            $totalShipping = (int) $invoice->order?->shipping_amount ?? 0;

            $totalNet = $totalItemsNet + $totalShipping;

            $filename = $invoice->slug . '-' . now()->format('Y-m-d');
            $pdf      = PDF::loadView('invoices.templates.pdf.invoice', [
                'shop'          => $invoice->shop,
                'invoice'       => $invoice,
                'transactions'  => $invoice->invoiceTransactions,
                'totalNet'      => $totalNet
            ]);

            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '.pdf"');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate PDF'], 500);
        }
    }

    /**
     * @throws \Mpdf\MpdfException
     */
    public function asController(Organisation $organisation, Invoice $invoice): Response
    {
        return $this->handle($invoice);
    }
}
