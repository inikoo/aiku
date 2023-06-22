<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Actions\Traits\WithExportData;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use  Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Symfony\Component\HttpFoundation\Response;

class ExportInvoice
{
    use AsAction;
    use WithAttributes;
    use WithExportData;

    /**
     * @throws \Mpdf\MpdfException
     */
    public function handle(): Response
    {
        $pdf = PDF::loadView('invoices.templates.pdf.invoice');

        return $pdf->stream('invoice.pdf');
    }

    /**
     * @throws \Mpdf\MpdfException
     */
    public function asController(): Response
    {
        return $this->handle();
    }
}
