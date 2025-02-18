<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:12:07 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Comms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Comms\SesNotification
 *
 * @property int $id
 * @property string $message_id
 * @property array<array-key, mixed> $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SesNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SesNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SesNotification onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SesNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SesNotification withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SesNotification withoutTrashed()
 * @mixin \Eloquent
 */
class SesNotification extends Model
{
    use SoftDeletes;

    protected $casts = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];
}
