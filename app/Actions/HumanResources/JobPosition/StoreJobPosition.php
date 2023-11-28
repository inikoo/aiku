<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Jun 2023 11:39:33 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition;

use App\Actions\Organisation\Group\Hydrators\GroupHydrateJobPositions;
use App\Models\HumanResources\JobPosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreJobPosition
{
    use AsAction;
    use WithAttributes;

    public function handle(array $modelData): JobPosition
    {
        $jobPosition = JobPosition::create($modelData);
        if($group=group()) {
            GroupHydrateJobPositions::run($group);
        }

        return $jobPosition;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("hr.edit");
    }


    public function rules(): array
    {
        return [
            'code' => ['required', 'iunique:job_positions', 'max:8', 'alpha_dash'],
            'name' => ['required', 'max:255'],
        ];
    }

    public function asController(ActionRequest $request): JobPosition
    {
        $request->validate();

        return $this->handle($request->validated());
    }

    public function htmlResponse(JobPosition $jobPosition): RedirectResponse
    {
        return Redirect::route('hr.job-positions.show', $jobPosition->slug);
    }
}
