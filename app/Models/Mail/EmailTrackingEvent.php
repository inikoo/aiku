<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 19:49:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use App\Enums\Mail\EmailTrackingEvent\EmailTrackingEventTypeEnum;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Mail\EmailTrackingEvent
 *
 * @property int $id
 * @property string|null $notification_id
 * @property int $dispatched_email_id
 * @property EmailTrackingEventTypeEnum $type
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $source_id
 * @method static Builder|EmailTrackingEvent newModelQuery()
 * @method static Builder|EmailTrackingEvent newQuery()
 * @method static Builder|EmailTrackingEvent query()
 * @mixin Eloquent
 */
class EmailTrackingEvent extends Model
{
    protected $casts = [
        'data'  => 'array',
        'type'  => EmailTrackingEventTypeEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];
}
