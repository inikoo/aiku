<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 09 Nov 2023 14:46:06 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use App\Enums\Mail\DispatchedEmailEvent\DispatchedEmailEventTypeEnum;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Mail\DispatchedEmailEvent
 *
 * @property int $id
 * @property \App\Enums\Mail\DispatchedEmailEvent\DispatchedEmailEventTypeEnum $type
 * @property int|null $dispatched_email_id
 * @property string $date
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmailEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmailEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmailEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmailEvent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmailEvent whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmailEvent whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmailEvent whereDispatchedEmailId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmailEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmailEvent whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmailEvent whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DispatchedEmailEvent extends Model
{
    protected $casts = [
        'data' => 'array',
        'type' => DispatchedEmailEventTypeEnum::class,
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];
}
