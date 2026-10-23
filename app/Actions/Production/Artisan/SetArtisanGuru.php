<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 16:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\Artisan;

use App\Actions\OrgAction;
use App\Models\HumanResources\Employee;
use App\Models\Production\Production;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class SetArtisanGuru extends OrgAction
{
    /**
     * The Guru bonus is worked out when payroll is read, so changing the dates changes every period they cover.
     */
    public function handle(Employee $employee, array $modelData): Employee
    {
        $guruSince = Arr::get($modelData, 'guru_since');

        $employee->update([
            'guru_since' => $guruSince,
            'guru_until' => $guruSince ? Arr::get($modelData, 'guru_until') : null,
        ]);

        return $employee;
    }

    public function rules(): array
    {
        return [
            'guru_since' => ['present', 'nullable', 'date'],
            'guru_until' => ['sometimes', 'nullable', 'date', 'after_or_equal:guru_since'],
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo([
            'org-supervisor.'.$this->organisation->id,
            "productions_operations.{$this->production->id}.orchestrate",
            "human-resources.{$this->organisation->id}.edit",
        ]);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }

    public function action(Production $production, Employee $employee, array $modelData): Employee
    {
        $this->asAction = true;
        $this->initialisationFromProduction($production, $modelData);

        return $this->handle($employee, $this->validatedData);
    }

    public function asController(Organisation $organisation, Production $production, Employee $employee, ActionRequest $request): Employee
    {
        abort_if($employee->organisation_id != $production->organisation_id, 404);
        $this->initialisationFromProduction($production, $request);

        return $this->handle($employee, $this->validatedData);
    }
}
