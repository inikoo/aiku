<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:22:54 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Profile;

use App\Actions\GrpAction;
use App\Actions\HumanResources\Timesheet\UI\IndexTimesheets;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Actions\UI\WithInertia;
use App\Enums\UI\SysAdmin\ProfileTabsEnum;
use App\Http\Resources\HumanResources\TimesheetsResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowProfileIndexTimesheets extends GrpAction
{
    use AsAction;
    use WithInertia;
    use WithActionButtons;

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation(group(), $request)->withTab(ProfileTabsEnum::values());

        return IndexTimesheets::run($request->user()->parent, ProfileTabsEnum::TIMESHEETS->value);
    }

    public function jsonResponse(LengthAwarePaginator $timesheets): AnonymousResourceCollection
    {
        return TimesheetsResource::collection($timesheets);
    }
}
