<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 09:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\Traits;

use App\Models\SysAdmin\User;
use Illuminate\Support\Arr;

/**
 * Stamps who set each stage date and when, whichever screen or action wrote it, in stage_marks.
 * A date written without a signed-in user (fetches, scheduled commands) keeps no stamp.
 */
trait HasStageMarks
{
    public static function bootHasStageMarks(): void
    {
        static::saving(function (self $model) {
            $user = auth()->user();
            if (!$user instanceof User) {
                return;
            }

            $marks = $model->stage_marks ?? [];
            foreach ($model->stageMarkColumns() as $column) {
                if (!$model->isDirty($column)) {
                    continue;
                }

                if ($model->{$column} === null) {
                    Arr::forget($marks, $column);
                } else {
                    $marks[$column] = [
                        'user_id' => $user->id,
                        'at'      => now()->toIso8601String(),
                    ];
                }
            }

            if ($marks !== ($model->stage_marks ?? [])) {
                $model->stage_marks = $marks;
            }
        });
    }

    public function stageMarkUserId(string $column): ?int
    {
        return Arr::get($this->stage_marks, "$column.user_id");
    }
}
