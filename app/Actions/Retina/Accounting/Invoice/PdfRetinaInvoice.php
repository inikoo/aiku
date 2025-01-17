<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 17 Jan 2025 08:44:57 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Accounting\Invoice;

use App\Actions\Accounting\Invoice\WithInvoicesExport;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithExportData;
use App\Models\Accounting\Invoice;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Symfony\Component\HttpFoundation\Response;

class PdfRetinaInvoice extends RetinaAction
{
    use AsAction;
    use WithAttributes;
    use WithExportData;
    use WithInvoicesExport;

    public function handle(Invoice $invoice): Response
    {
        return $this->processDataExportPdf($invoice);
    }

    public function asController(Invoice $invoice): Response
    {
        return $this->handle($invoice);
    }
}
