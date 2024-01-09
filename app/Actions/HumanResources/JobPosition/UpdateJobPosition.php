<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Jun 2023 11:39:33 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition;

use App\Actions\InertiaOrganisationAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\HumanResources\JobPositionResource;
use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class UpdateJobPosition extends InertiaOrganisationAction
{
    use WithActionUpdate;

    public function handle(JobPosition $jobPosition, array $modelData): JobPosition
    {
        return $this->update($jobPosition, $modelData, ['data']);
    }


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->slug}.edit");
    }

    public function rules(): array
    {
        return [
            'code'      => ['sometimes','required', 'max:8'],
            'name'      => ['sometimes','required', 'max:255'],
        ];
    }

    public function asController(Organisation $organisation, JobPosition $jobPosition, ActionRequest $request): JobPosition
    {
        $request->validate();

        return $this->handle($jobPosition, $request->all());
    }


    public function jsonResponse(JobPosition $jobPosition): JobPositionResource
    {
        return new JobPositionResource($jobPosition);
    }
}
