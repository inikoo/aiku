<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Mar 2023 16:46:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Mailroom\Mailroom
 *
 * @property int $id
 * @property string $code
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mail\Outbox> $outboxes
 * @property-read \App\Models\Mail\MailroomStats|null $stats
 * @method static Builder|Mailroom newModelQuery()
 * @method static Builder|Mailroom newQuery()
 * @method static Builder|Mailroom query()
 * @mixin \Eloquent
 */
class Mailroom extends Model
{
    use UsesTenantConnection;

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
