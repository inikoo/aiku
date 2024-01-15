<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 09 Nov 2023 14:46:06 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
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
 * @method static \Illuminate\Database\Eloquent\Builder|SesNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SesNotification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SesNotification whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SesNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SesNotification whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SesNotification whereUpdatedAt($value)
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
