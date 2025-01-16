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
use Symfony\Component\HttpFoundation\Response;

class PdfInvoice
{
    use AsAction;
    use WithAttributes;
    use WithExportData;
    use WithInvoicesExport;

    /**
     * @throws \Mpdf\MpdfException
     */
    public function handle(Invoice $invoice): Response
    {
        return $this->processDataExportPdf($invoice);
    }

    /**
     * @throws \Mpdf\MpdfException
     */
    public function asController(Organisation $organisation, Invoice $invoice): Response
    {
        return $this->handle($invoice);
    }
}
