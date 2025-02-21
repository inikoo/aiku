<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 14:54:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Timesheet\Pdf;

use App\Actions\Traits\WithExportData;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Symfony\Component\HttpFoundation\Response;

class PdfTimesheet
{
    use AsAction;
    use WithAttributes;
    use WithExportData;

    /**
     * @throws \Mpdf\MpdfException
     */
    public function handle(Organisation $organisation)
    {
        $filename = __('Timesheets - ') . $organisation->name . '.pdf';
        $config = [
            'title'                  => $filename,
            'margin_left'            => 8,
            'margin_right'           => 8,
            'margin_top'             => 2,
            'margin_bottom'          => 2,
            'auto_page_break'        => true,
            'auto_page_break_margin' => 10
        ];

        return PDF::chunkLoadView('<html-separator/>', 'hr.timesheets', [
            'filename' => $filename,
            'organisation' => $organisation,
            'employees' => $organisation->employees()->limit(10)->get(),
        ], [], $config)->stream($filename);
    }

    /**
     * @throws \Mpdf\MpdfException
     */
    public function asController(Organisation $organisation, ActionRequest $request): Response
    {
        return $this->handle($organisation);
    }
}
