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
use App\Models\SysAdmin\User;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DetachUserFromTask
{
    use AsAction;
    
    public function handle( Task $task, $user): void
    {
        $task->users()->detach($user->id);
    }


    public function asController( Task $task, $user, ): void
    {
        $this->handle($task, $user);
    }
}