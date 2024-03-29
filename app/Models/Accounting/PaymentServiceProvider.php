<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 10:07:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Accounting;

use App\Enums\Accounting\PaymentServiceProvider\PaymentServiceProviderTypeEnum;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Payments\PaymentServiceProvider
 *
 * @property int $id
 * @property string $slug
 * @property int $group_id
 * @property int $organisation_id
 * @property PaymentServiceProviderTypeEnum $type
 * @property string $code
 * @property string $name
 * @property array $data
 * @property string|null $last_used_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read Collection<int, \App\Models\Accounting\PaymentAccount> $accounts
 * @property-read Group $group
 * @property-read Organisation $organisation
 * @property-read Collection<int, \App\Models\Accounting\Payment> $payments
 * @property-read \App\Models\Accounting\PaymentServiceProviderStats|null $stats
 * @method static \Database\Factories\Accounting\PaymentServiceProviderFactory factory($count = null, $state = [])
 * @method static Builder|PaymentServiceProvider newModelQuery()
 * @method static Builder|PaymentServiceProvider newQuery()
 * @method static Builder|PaymentServiceProvider onlyTrashed()
 * @method static Builder|PaymentServiceProvider query()
 * @method static Builder|PaymentServiceProvider withTrashed()
 * @method static Builder|PaymentServiceProvider withoutTrashed()
 * @mixin Eloquent
 */
class PaymentServiceProvider extends Model
{
    use SoftDeletes;
    use HasSlug;

    use HasFactory;

    protected $casts = [
        'data' => 'array',
        'type' => PaymentServiceProviderTypeEnum::class
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

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, PaymentAccount::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(PaymentAccount::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(PaymentServiceProviderStats::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
