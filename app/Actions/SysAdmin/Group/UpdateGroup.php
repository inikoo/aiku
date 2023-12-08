<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:14:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group;

use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\SysAdmin\Group\GroupResource;
use App\Models\SysAdmin\Group;
use Lorisleiva\Actions\ActionRequest;

class UpdateGroup
{
    use WithActionUpdate;


    private bool $asAction = false;

    public function handle(Group $group, array $modelData): Group
    {
        return $this->update($group, $modelData);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("sysadmin.edit");
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:64'],
        ];
    }

    public function action(Group $group, array $modelData): Group
    {
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($group, $validatedData);
    }

    public function asController(Group $group, ActionRequest $request): Group
    {
        $request->validate();

        return $this->handle($group, $request->all());
    }


    public function jsonResponse(Group $group): GroupResource
    {
        return new GroupResource($group);
    }
}
