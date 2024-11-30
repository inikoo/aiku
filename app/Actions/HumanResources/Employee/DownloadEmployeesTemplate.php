<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 08:23:57 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee;

use App\Exports\HumanResources\EmployeesTemplateExport;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadEmployeesTemplate
{
    use AsAction;
    use WithAttributes;

    public function handle(): BinaryFileResponse
    {

        return Excel::download(new EmployeesTemplateExport(), 'employees_template.xlsx');
    }

    public function asController(Organisation $organisation): BinaryFileResponse
    {
        return $this->handle();
    }

}
