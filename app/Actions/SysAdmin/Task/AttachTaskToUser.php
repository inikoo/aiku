<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:24:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Task;

use App\Enums\Task\TaskStatusEnum;
use App\Models\SysAdmin\User;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class AttachTasktoUser
{
    use AsAction;

    public function handle(User $user, $task, array $pivotData): void
    {
        $user->tasks()->attach($task->id, $pivotData);
    }

    public function rules(): array
    {
        return [
            'user_id'       => ['required', 'exists:users,id'],
            'task_id'       => ['required', 'exists:tasks,id'],
            'start_date'    => ['sometimes', 'date'],
            'complete_date' => ['sometimes', 'date'],
            'deadline'      => ['sometimes', 'date'],
            'status'        => ['sometimes', 'string', Rule::in(TaskStatusEnum::values())],
        ];
    }

    public function asController(User $user, $task, ActionRequest $request): void
    {
        $data = $request->validate($this->rules());

        $pivotData = Arr::except($data, ['user_id', 'task_id']);

        $this->handle($user, $task, $pivotData);
    }
}