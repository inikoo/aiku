<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Jun 2023 11:39:33 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\GroupJobPosition;

use App\Actions\GrpAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\HumanResources\JobPosition\JobPositionScopeEnum;
use App\Models\SysAdmin\GroupJobPosition;
use Illuminate\Validation\Rule;

class UpdateGroupJobPosition extends GrpAction
{
    use WithActionUpdate;

    public function handle(GroupJobPosition $groupJobPosition, array $modelData): GroupJobPosition
    {
        return $this->update($groupJobPosition, $modelData, ['data']);
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


    public function action(GroupJobPosition $groupJobPosition, array $modelData): GroupJobPosition
    {
        $this->initialisation($groupJobPosition->group, $modelData);

        return $this->handle($groupJobPosition, $this->validatedData);
    }


}
