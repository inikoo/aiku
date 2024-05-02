<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\OrgAction;
use App\Actions\Traits\WithExportData;
use App\Exports\HumanResources\TimesheetsExport;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportEmployeeTimesheets extends OrgAction
{
    use WithExportData;

    /**
     * @throws \Throwable
     */
    public function handle(Employee $employee, array $modelData): BinaryFileResponse
    {
        $type = $modelData['type'];

        return $this->export(new TimesheetsExport($employee), 'employee-timesheets', $type);
    }

    /**
     * @throws \Throwable
     */
    public function asController(Organisation $organisation, Employee $employee, ActionRequest $request): BinaryFileResponse
    {
        $this->setRawAttributes($request->all());
        $this->validateAttributes();

        return $this->handle($employee, $request->all());
    }
}
