<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Nov 2023 16:48:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\Mail\Mail
 *
 * @property int $id
 * @property string $code
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\Mail\Outbox> $outboxes
 * @property-read int|null $outboxes_count
 * @property-read \App\Models\Mail\MailroomStats|null $stats
 * @method static Builder|Mailroom newModelQuery()
 * @method static Builder|Mailroom newQuery()
 * @method static Builder|Mailroom query()
 * @method static Builder|Mailroom whereCode($value)
 * @method static Builder|Mailroom whereCreatedAt($value)
 * @method static Builder|Mailroom whereData($value)
 * @method static Builder|Mailroom whereId($value)
 * @method static Builder|Mailroom whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Mailroom extends Model
{
    use HasFactory;

    protected $casts = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function stats(): HasOne
    {
        return $this->hasOne(MailroomStats::class);
    }

    public function outboxes(): HasMany
    {
        return $this->hasMany(Outbox::class);
    }
}
