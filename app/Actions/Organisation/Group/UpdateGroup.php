<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Organisation\Group;

use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Central\Group\GroupResource;
use App\Models\Organisation\Group;
use Lorisleiva\Actions\ActionRequest;

class UpdateGroup
{
    use WithActionUpdate;

    public function handle(Group $group, array $modelData): Group
    {
        return $this->update($group, $modelData);
    }

    //    public function authorize(ActionRequest $request): bool
    //    {
    //        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    //    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required'],
        ];
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
