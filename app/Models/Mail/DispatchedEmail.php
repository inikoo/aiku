<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use App\Enums\Mail\DispatchedEmail\DispatchedEmailStateEnum;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Mail\EmailDispatch
 *
 * @property int $id
 * @property int|null $outbox_id
 * @property int|null $mailshot_id
 * @property int|null $email_address_id
 * @property string|null $ses_id
 * @property string|null $recipient_type
 * @property int|null $recipient_id
 * @property DispatchedEmailStateEnum $state
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $sent_at
 * @property string|null $first_read_at
 * @property string|null $last_read_at
 * @property string|null $first_clicked_at
 * @property string|null $last_clicked_at
 * @property int $number_reads
 * @property int $number_clicks
 * @property bool $mask_as_spam
 * @property bool $provoked_unsubscribe
 * @property int|null $source_id
 * @property-read Collection<int, \App\Models\Mail\EmailTrackingEvent> $emailTrackingEvents
 * @method static \Database\Factories\Mail\DispatchedEmailFactory factory($count = null, $state = [])
 * @method static Builder|DispatchedEmail newModelQuery()
 * @method static Builder|DispatchedEmail newQuery()
 * @method static Builder|DispatchedEmail query()
 * @mixin Eloquent
 */
class DispatchedEmail extends Model
{
    use UsesTenantConnection;
    use HasFactory;

    protected $casts = [
        'state'  => DispatchedEmailStateEnum::class,
        'sent_at'=> 'datetime'
    ];

    protected $guarded = [];

    public function emailTrackingEvents(): HasMany
    {
        return $this->hasMany(EmailTrackingEvent::class);
    }
    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
