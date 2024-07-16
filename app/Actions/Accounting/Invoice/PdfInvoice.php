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
use Mccarlosen\LaravelMpdf\LaravelMpdf;
use Mpdf\MpdfException;
use Symfony\Component\HttpFoundation\Response;

class PdfInvoice
{
    use AsAction;
    use WithAttributes;
    use WithExportData;

    public string $filename;

    /**
     * @throws \Mpdf\MpdfException
     */
    public function handle(Invoice $invoice): LaravelMpdf|\PDF
    {
        $totalItemsNet = (int) $invoice->total_amount;
        $totalShipping = (int) $invoice->order->shipping;

        $totalNet = $totalItemsNet + $totalShipping;

        $this->filename = $invoice->slug . '-' . now()->format('Y-m-d');

        return PDF::loadView('invoices.templates.pdf.invoice', [
            'invoice'       => $invoice,
            'transactions'  => $invoice->invoiceTransactions,
            'totalNet'      => $totalNet
        ]);
    }

    /**
     * @throws \Mpdf\MpdfException
     */
    public function asController(Organisation $organisation, Invoice $invoice): Response
    {
        return $this->handle($invoice)->stream($this->filename);
    }

    public function asSave(Invoice $invoice): MpdfException
    {
        return $this->handle($invoice)->save($this->filename);
    }
}
