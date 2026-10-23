<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 15:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\Chat;

use App\Enums\CRM\Livechat\ChatAiRunKindEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One pass of the AI customer service sweep over the inboxes, so what it did can be read run by run.
 *
 * @property int $id
 * @property int $group_id
 * @property ChatAiRunKindEnum $kind
 * @property array $teams
 * @property string|null $model
 * @property int|null $started_by_user_id
 * @property \Illuminate\Support\Carbon $started_at
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property array $counts
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ChatAiOutcome> $outcomes
 */
class ChatAiRun extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $attributes = [
        'teams'  => '[]',
        'counts' => '{}',
    ];

    protected function casts(): array
    {
        return [
            'kind'        => ChatAiRunKindEnum::class,
            'teams'       => 'array',
            'counts'      => 'array',
            'started_at'  => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function outcomes(): HasMany
    {
        return $this->hasMany(ChatAiOutcome::class, 'run_id');
    }
}
