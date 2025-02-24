<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 14:54:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Timesheet\Pdf;

use App\Actions\Traits\WithExportData;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
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
    public function handle(Organisation|Employee $parent)
    {
        $filename = __('Timesheets - ') . $parent->name . '.pdf';
        $config = [
            'title' => $filename,
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 2,
            'margin_bottom' => 2,
            'auto_page_break' => true,
            'auto_page_break_margin' => 10
        ];

        $query = QueryBuilder::for(Employee::class);

        if ($parent instanceof Organisation) {
            $organisation = $parent;
            $query->where('organisation_id', $parent->id);
        } else {
            $organisation = $parent->organisation;
            $query->where('id', $parent->id);
        }

        $query->whereHas('timesheets', function ($q) {
            QueryBuilder::for($q)->withFilterPeriod('date');
        });

        $chunkSize = 10;
        $employeeChunks = $query->get()->chunk($chunkSize);

        foreach ($employeeChunks as $key => $chunk) {
            return PDF::chunkLoadView('<html-separator/>', 'hr.timesheets', [
                'filename' => $filename,
                'organisation' => $organisation,
                'employees' => $chunk
            ], [], $config)->stream($filename);
        }
    }

    /**
     * @throws \Mpdf\MpdfException
     */
    public function asController(Organisation $organisation, ActionRequest $request): Response
    {
        return $this->handle($organisation);
    }

    /**
     * @throws \Mpdf\MpdfException
     */
    public function inEmployee(Organisation $organisation, Employee $employee, ActionRequest $request): Response
    {
        return $this->handle($employee);
    }
}
