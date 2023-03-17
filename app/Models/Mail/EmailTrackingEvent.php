<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 19:49:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use App\Enums\Mailroom\EmailTrackingEvent\EmailTrackingEventTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Mailroom\EmailTrackingEvent
 *
 * @property int $id
 * @property string|null $notification_id
 * @property int $dispatched_email_id
 * @property EmailTrackingEventTypeEnum $type
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $source_id
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTrackingEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTrackingEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTrackingEvent query()
 * @mixin \Eloquent
 */
class EmailTrackingEvent extends Model
{
    use UsesTenantConnection;

    protected $casts = [
        'data'  => 'array',
        'type'  => EmailTrackingEventTypeEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];
}
