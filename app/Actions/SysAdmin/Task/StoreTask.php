<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:24:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Task;

use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Task;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreTask
{
    use AsAction;
    use WithAttributes;

    public function handle(Group $group, $modelData): Task
    {
        data_set($modelData, 'group_id', $group->id);

        $task = Task::create($modelData);

        return $task;
    }

    public function rules(): array
    {
        return [
            'code'              => ['required','alpha_dash','max:64','unique:tasks,code'],
            'organisation_id'   => ['sometimes', 'exists:organisations,id'],
            'name'              => ['required', 'string', 'max:255'],
            'description'       => ['sometimes', 'string'],
        ];
    }

    public function asController(Group $group): Task
    {
        return $this->handle($group, $this->validatedData);
    }
}
