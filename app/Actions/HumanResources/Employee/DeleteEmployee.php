<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 13:12:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee;

use App\Models\HumanResources\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteEmployee
{
    use AsController;
    use WithAttributes;

    public function handle(Employee $employee): Employee
    {
        $employee->delete();

        return $employee;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->slug}.edit");
    }

    public function asController(Employee $employee, ActionRequest $request): Employee
    {
        $request->validate();

        return $this->handle($employee);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::route('grp.hr.employees.index');
    }

}
