<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 12:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Reports;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithAccountingReportsAuthorisation;
use App\Exports\Reports\CostOfGoodsSoldExport;
use App\Models\SysAdmin\Organisation;
use Carbon\Carbon;
use Lorisleiva\Actions\ActionRequest;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportCostOfGoodsSold extends OrgAction
{
    use WithAccountingReportsAuthorisation;

    public function handle(Organisation $organisation, string $startDate, string $endDate): BinaryFileResponse
    {
        return Excel::download(
            new CostOfGoodsSoldExport($organisation->id, $startDate, $endDate),
            sprintf('cogs-%s-%s-to-%s.xlsx', $organisation->slug, $startDate, $endDate),
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function asController(Organisation $organisation, ActionRequest $request): BinaryFileResponse
    {
        $this->initialisation($organisation, $request);

        return $this->handle(
            $organisation,
            Carbon::parse($this->validatedData['start_date'])->toDateString(),
            Carbon::parse($this->validatedData['end_date'])->toDateString()
        );
    }
}
