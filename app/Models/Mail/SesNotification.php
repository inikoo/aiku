<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Jan 2024 12:27:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Mail\SesNotification
 *
 * @property int $id
 * @property string $message_id
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|SesNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SesNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SesNotification onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SesNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder|SesNotification withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SesNotification withoutTrashed()
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
