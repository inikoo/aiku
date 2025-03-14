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
use App\Models\Fulfilment\Pallet;
use Carbon\Carbon;
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

            $transactions = $invoice->invoiceTransactions->map(function ($transaction) {
                if (!empty($transaction->data['pallet_id'])) {
                    $pallet = Pallet::find($transaction->data['pallet_id']);
                    $transaction->pallet = $pallet->reference;
                }

                if (!empty($transaction->data['date'])) {
                    $transaction->handling_date = Carbon::parse($transaction->data['date'])->format('d M Y');
                }

                return $transaction;
            });

            $config = [
                'title'                  => 'hello'.$invoice->reference,
                'margin_left'            => 8,
                'margin_right'           => 8,
                'margin_top'             => 2,
                'margin_bottom'          => 2,
                'auto_page_break'        => true,
                'auto_page_break_margin' => 10
            ];

            $filename = $invoice->slug . '-' . now()->format('Y-m-d');
            $pdf      = PDF::loadView('invoices.templates.pdf.invoice', [
                'shop'          => $invoice->shop,
                'invoice'       => $invoice,
                'transactions'  => $transactions,
                'totalNet'      => $totalNet
            ], [], $config);

            return response($pdf->stream(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '.pdf"');
        } catch (Exception $e) {
            dd($e);
            return response()->json(['error' => 'Failed to generate PDF'], 404);
        }
    }
}
