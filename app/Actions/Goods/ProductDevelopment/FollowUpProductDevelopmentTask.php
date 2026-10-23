<?php

/*
 * Author Louis Perez
 * Created on 16-10-2026-18h-50m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment;

use App\Enums\Tasks\StaffTaskStatusEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\SysAdmin\User;
use App\Models\Tasks\StaffTask;
use Lorisleiva\Actions\Concerns\AsAction;

class FollowUpProductDevelopmentTask
{
    use AsAction;

    public function handle(StaffTask $task, User $actor): void
    {
        if ($task->model_type !== 'ProductDevelopment' || $task->status !== StaffTaskStatusEnum::DONE) {
            return;
        }

        $productDevelopment = ProductDevelopment::find($task->model_id);
        if (!$productDevelopment) {
            return;
        }

        CreateProductDevelopmentTask::make()->ensureForDevelopment($productDevelopment, $actor);
    }
}
