<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 10-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Accounting\Invoice;

use App\Models\Accounting\Invoice;
use Exception;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

trait WithInvoicesExport
{
    public function processDataExportPdf(Invoice $invoice): \Symfony\Component\HttpFoundation\Response
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
        } catch (Exception) {
            return response()->json(['error' => 'Failed to generate PDF'], 404);
        }
    }
}
