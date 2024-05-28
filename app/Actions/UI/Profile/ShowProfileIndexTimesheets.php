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
use App\Models\SysAdmin\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowProfileIndexTimesheets extends GrpAction
{
    use AsAction;
    use WithInertia;
    use WithActionButtons;

    public function asController(ActionRequest $request): User
    {
        $this->initialisation(group(), $request)->withTab(ProfileTabsEnum::values());

        return $request->user();
    }

    public function jsonResponse(User $user): AnonymousResourceCollection
    {
        return TimesheetsResource::collection(IndexTimesheets::run($user->parent, ProfileTabsEnum::TIMESHEETS->value));
    }
}
