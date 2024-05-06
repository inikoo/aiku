<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Jun 2023 11:39:33 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\HumanResources\JobPosition\JobPositionScopeEnum;
use App\Http\Resources\HumanResources\JobPositionResource;
use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Organisation;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateJobPosition extends OrgAction
{
    use WithActionUpdate;

    public function handle(JobPosition $jobPosition, array $modelData): JobPosition
    {
        return $this->update($jobPosition, $modelData, ['data']);
    }


    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }

    public function rules(): array
    {
        return [
            'code'       => ['sometimes', 'required', 'max:8'],
            'name'       => ['sometimes', 'required', 'max:255'],
            'scope'      => ['required', Rule::enum(JobPositionScopeEnum::class)],
            'department' => ['sometimes', 'nullable', 'string'],
            'team'       => ['sometimes', 'nullable', 'string']
        ];
    }

    public function asController(Organisation $organisation, JobPosition $jobPosition, ActionRequest $request): JobPosition
    {
        $request->validate();

        return $this->handle($jobPosition, $request->all());
    }

    public function action(JobPosition $jobPosition, array $modelData): JobPosition
    {
        $this->asAction = true;
        $this->initialisation($jobPosition->organisation, $modelData);

        return $this->handle($jobPosition, $this->validatedData);
    }

    public function jsonResponse(JobPosition $jobPosition): JobPositionResource
    {
        return new JobPositionResource($jobPosition);
    }
}
