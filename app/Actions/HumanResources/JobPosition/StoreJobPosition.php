<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Jun 2023 11:39:33 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition;

use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateJobPositions;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateJobPositions;
use App\Enums\HumanResources\JobPosition\JobPositionScopeEnum;
use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreJobPosition extends OrgAction
{
    public function handle(Organisation $organisation, array $modelData): JobPosition
    {
        data_set($modelData, 'group_id', $organisation->group_id);
        /** @var JobPosition $jobPosition */
        $jobPosition = $organisation->josPositions()->create($modelData);

        GroupHydrateJobPositions::run($organisation->group);
        OrganisationHydrateJobPositions::run($organisation);

        return $jobPosition;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("org-supervisor.{$this->organisation->id}.human-resources");
    }


    public function rules(): array
    {
        return [
            'code'       => [
                'required',
                new IUnique(
                    table: 'job_positions',
                    extraConditions: [
                        ['column' => 'organisation_id', 'value' => $this->organisation->id]
                    ],
                ),
                'max:8',
                'alpha_dash'
            ],
            'name'       => ['required', 'max:255'],
            'locked'     => ['sometimes', 'boolean'],
            'scope'      => ['required', Rule::enum(JobPositionScopeEnum::class)],
            'department' => ['sometimes', 'nullable', 'string'],
            'team'       => ['sometimes', 'nullable', 'string']
        ];
    }

    public function prepareForValidation(): void
    {
        if (!$this->asAction) {
            if ($this->has('code')) {
                $this->set('code', 'c-'.$this->get('code'));
                $this->set('locked', false);
            }
        }
    }

    public function action(Organisation $organisation, array $modelData): JobPosition
    {
        $this->asAction = true;
        $this->initialisation($organisation, $modelData);

        return $this->handle($organisation, $this->validatedData);
    }

    public function asController(Organisation $organisation, ActionRequest $request): JobPosition
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation, $this->validatedData);
    }

    public function htmlResponse(JobPosition $jobPosition): RedirectResponse
    {
        return Redirect::route('grp.org.hr.job-positions.show', [
            $this->organisation->slug,
            $jobPosition->slug
        ]);
    }
}
