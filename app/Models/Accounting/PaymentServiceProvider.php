<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 10:07:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Stancl\Tenancy\Database\Concerns\TenantConnection;

/**
 * App\Models\Payments\PaymentServiceProvider
 *
 * @property int $id
 * @property string $type
 * @property string $block
 * @property string $code
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accounting\PaymentAccount> $accounts
 * @property-read int|null $accounts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accounting\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \App\Models\Accounting\PaymentServiceProviderStats|null $stats
 * @method static Builder|PaymentServiceProvider newModelQuery()
 * @method static Builder|PaymentServiceProvider newQuery()
 * @method static Builder|PaymentServiceProvider onlyTrashed()
 * @method static Builder|PaymentServiceProvider query()
 * @method static Builder|PaymentServiceProvider whereBlock($value)
 * @method static Builder|PaymentServiceProvider whereCode($value)
 * @method static Builder|PaymentServiceProvider whereCreatedAt($value)
 * @method static Builder|PaymentServiceProvider whereDeletedAt($value)
 * @method static Builder|PaymentServiceProvider whereId($value)
 * @method static Builder|PaymentServiceProvider whereSlug($value)
 * @method static Builder|PaymentServiceProvider whereSourceId($value)
 * @method static Builder|PaymentServiceProvider whereType($value)
 * @method static Builder|PaymentServiceProvider whereUpdatedAt($value)
 * @method static Builder|PaymentServiceProvider withTrashed()
 * @method static Builder|PaymentServiceProvider withoutTrashed()
 * @mixin \Eloquent
 */
class PaymentServiceProvider extends Model
{
    use SoftDeletes;
    use HasSlug;
    use TenantConnection;

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
            ->saveSlugsTo('slug');
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class,PaymentAccount::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(PaymentAccount::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(PaymentServiceProviderStats::class);
    }

}
