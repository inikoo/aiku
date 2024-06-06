<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:24:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Task;

use App\Enums\Task\TaskStatusEnum;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Task;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreTask
{
    use AsAction;
    use WithAttributes;

    public function handle(Task $task, $modelData): Task
    {
        $task->update($modelData);

        return $task;
    }

    public function rules(): array
    {
        return [
            'organisation_id'   => ['sometimes', 'exists:organisations,id'],
            'name'              => ['sometimes', 'string', 'max:255'],
            'description'       => ['sometimes', 'string'],
        ];
    }

    public function asController(ActionRequest $request, Task $task): Task
    {

        return $this->handle($task, $this->validatedData);
    }
}