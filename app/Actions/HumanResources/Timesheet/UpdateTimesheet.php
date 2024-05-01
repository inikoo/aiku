<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Apr 2024 15:53:53 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Timesheet;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\HumanResources\Timesheet;
use Lorisleiva\Actions\ActionRequest;

class UpdateTimesheet extends OrgAction
{
    use WithActionUpdate;



    public function handle(Timesheet $timesheet, array $modelData): Timesheet
    {


        $timesheet = $this->update($timesheet, $modelData, ['data']);

        return $timesheet;
    }


    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }
        return false;
    }

    public function rules(): array
    {
        return [

        ];
    }

    public function action(Timesheet $timesheet, $modelData): Timesheet
    {
        $this->asAction=true;
        $this->initialisation($timesheet->organisation, $modelData);

        return $this->handle($timesheet, $this->validatedData);
    }


}
