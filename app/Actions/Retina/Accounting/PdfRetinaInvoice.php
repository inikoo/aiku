<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Accounting;

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
