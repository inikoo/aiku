<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Group;

use App\Actions\WithActionUpdate;
use App\Http\Resources\Central\Group\GroupResource;
use App\Models\Central\Group;
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
