<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Models\Web;

use App\Models\SysAdmin\Group;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $group_id
 * @property string $account_id
 * @property string $account_name
 * @property string $container_id
 * @property string $name
 * @property string $public_id
 * @property string|null $usage_context
 * @property array<int, string>|null $domain_names
 * @property string|null $tag_manager_url
 * @property \Illuminate\Support\Carbon|null $fetched_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Group $group
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoogleTagManagerContainer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoogleTagManagerContainer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoogleTagManagerContainer query()
 * @mixin \Eloquent
 */
class GoogleTagManagerContainer extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'domain_names' => 'array',
            'fetched_at'   => 'datetime',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
