<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 24 Nov 2024 10:15:14 Central Indonesia Time, Kuta, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Comms;

use App\Enums\Comms\Email\EmailRunStateEnum;
use App\Enums\Comms\EmailRun\EmailRunTypeEnum;
use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $shop_id
 * @property string $subject
 * @property int|null $outbox_id
 * @property int|null $email_id
 * @property int|null $snapshot_id
 * @property EmailRunStateEnum $state
 * @property EmailRunTypeEnum $type
 * @property \Illuminate\Support\Carbon|null $scheduled_at
 * @property \Illuminate\Support\Carbon|null $start_sending_at
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property \Illuminate\Support\Carbon|null $stopped_at
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $fetched_at
 * @property string|null $last_fetched_at
 * @property string|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comms\DispatchedEmail> $dispatchedEmails
 * @property-read \App\Models\Comms\Email|null $email
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Comms\Outbox|null $outbox
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @property-read \App\Models\Comms\EmailRunStats|null $stats
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailRun newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailRun newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailRun query()
 * @mixin \Eloquent
 */
class EmailRun extends Model
{
    use InShop;

    protected $casts = [
        'data'              => 'array',
        'type'              => EmailRunTypeEnum::class,
        'state'             => EmailRunStateEnum::class,
        'date'              => 'datetime',
        'scheduled_at'      => 'datetime',
        'start_sending_at'  => 'datetime',
        'sent_at'           => 'datetime',
        'cancelled_at'      => 'datetime',
        'stopped_at'        => 'datetime',
    ];

    protected $attributes = [
        'data'              => '{}',
    ];

    protected $guarded = [];

    public function generateTags(): array
    {
        return [
            'marketing'
        ];
    }

    protected array $auditInclude = [
        'subject',
        'schedule_at',
    ];


    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }

    public function outbox(): BelongsTo
    {
        return $this->belongsTo(Outbox::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(EmailRunStats::class);
    }

    public function dispatchedEmails(): HasMany
    {
        return $this->hasMany(DispatchedEmail::class);
    }


}
