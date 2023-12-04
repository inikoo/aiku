<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 10:07:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Accounting;

use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasHistory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Accounting\PaymentAccount
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $payment_service_provider_id
 * @property string $code
 * @property string $slug
 * @property string $name
 * @property array $data
 * @property string|null $last_used_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read Organisation $organisation
 * @property-read \App\Models\Accounting\PaymentServiceProvider $paymentServiceProvider
 * @property-read Collection<int, \App\Models\Accounting\Payment> $payments
 * @property-read \App\Models\Accounting\PaymentAccountStats|null $stats
 * @method static \Database\Factories\Accounting\PaymentAccountFactory factory($count = null, $state = [])
 * @method static Builder|PaymentAccount newModelQuery()
 * @method static Builder|PaymentAccount newQuery()
 * @method static Builder|PaymentAccount onlyTrashed()
 * @method static Builder|PaymentAccount query()
 * @method static Builder|PaymentAccount withTrashed()
 * @method static Builder|PaymentAccount withoutTrashed()
 * @mixin Eloquent
 */
class PaymentAccount extends Model implements Auditable
{
    use SoftDeletes;
    use HasSlug;

    use HasFactory;
    use HasHistory;

    protected $casts = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function paymentServiceProvider(): BelongsTo
    {
        return $this->belongsTo(PaymentServiceProvider::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(PaymentAccountStats::class);
    }
}
