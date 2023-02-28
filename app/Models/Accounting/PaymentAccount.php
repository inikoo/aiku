<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 10:07:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Accounting;

use App\Actions\Central\Tenant\Hydrators\TenantHydrateAccounting;
use App\Actions\Accounting\PaymentServiceProvider\Hydrators\PaymentServiceProviderHydrateAccounts;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Payments\PaymentAccount
 *
 * @property int $id
 * @property int $payment_service_provider_id
 * @property string $code
 * @property string $slug
 * @property string $name
 * @property array $data
 * @property string|null $last_used_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \App\Models\Accounting\PaymentServiceProvider $paymentServiceProvider
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accounting\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \App\Models\Accounting\PaymentAccountStats|null $stats
 * @method static Builder|PaymentAccount newModelQuery()
 * @method static Builder|PaymentAccount newQuery()
 * @method static Builder|PaymentAccount onlyTrashed()
 * @method static Builder|PaymentAccount query()
 * @method static Builder|PaymentAccount whereCode($value)
 * @method static Builder|PaymentAccount whereCreatedAt($value)
 * @method static Builder|PaymentAccount whereData($value)
 * @method static Builder|PaymentAccount whereDeletedAt($value)
 * @method static Builder|PaymentAccount whereId($value)
 * @method static Builder|PaymentAccount whereLastUsedAt($value)
 * @method static Builder|PaymentAccount whereName($value)
 * @method static Builder|PaymentAccount wherePaymentServiceProviderId($value)
 * @method static Builder|PaymentAccount whereSlug($value)
 * @method static Builder|PaymentAccount whereSourceId($value)
 * @method static Builder|PaymentAccount whereUpdatedAt($value)
 * @method static Builder|PaymentAccount withTrashed()
 * @method static Builder|PaymentAccount withoutTrashed()
 * @mixin \Eloquent
 */
class PaymentAccount extends Model
{
    use SoftDeletes;
    use HasSlug;

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

    protected static function booted()
    {
        static::created(
            function (PaymentAccount $paymentAccount) {
                TenantHydrateAccounting::dispatch(tenant());
                PaymentServiceProviderHydrateAccounts::dispatch($paymentAccount->paymentServiceProvider);
            }
        );
    }


    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug');
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
