<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Jun 2023 11:39:33 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition;

use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateJobPositions;
use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreJobPosition extends OrgAction
{
    use AsAction;
    use WithAttributes;

    /**
     * @var true
     */
    private bool $trusted;

    public function handle(Group $group, array $modelData): JobPosition
    {
        /** @var JobPosition $jobPosition */
        $jobPosition = $group->josPositions()->create($modelData);

        GroupHydrateJobPositions::run($group);


        return $jobPosition;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->trusted) {
            return true;
        }
        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->slug}.edit");
    }


    public function rules(): array
    {
        return [
            'code' => ['required',
                       new IUnique(
                           table: 'job_positions',
                           extraConditions: [
                               ['column' => 'group_id', 'value' => app('group')->id]
                           ],
                       ),
                        'max:8', 'alpha_dash'],
            'name' => ['required', 'max:255'],
        ];
    }

    public function action(Group $group, array $modelData): JobPosition
    {
        $this->trusted = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($group, $validatedData);
    }

    public function asController(Organisation $organisation, ActionRequest $request): JobPosition
    {
        $request->validate();

        return $this->handle(app('group'), $request->validated());
    }

    public function htmlResponse(JobPosition $jobPosition): RedirectResponse
    {
        return Redirect::route('grp.org.hr.job-positions.show', [
            $this->organisation->slug,
            $jobPosition->slug
        ]);
    }
}
