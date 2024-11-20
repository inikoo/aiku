<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 10 Sept 2024 21:54:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Discounts;

use App\Enums\Discounts\OfferComponent\OfferComponentStateEnum;
use App\Models\Traits\HasHistory;
use App\Models\Traits\InShop;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $shop_id
 * @property int $offer_campaign_id
 * @property int $offer_id
 * @property OfferComponentStateEnum $state
 * @property bool $status
 * @property string $slug
 * @property string $code
 * @property array $data
 * @property string|null $trigger_scope
 * @property string|null $trigger_type
 * @property string|null $trigger_id
 * @property string|null $target_type
 * @property bool $is_discretionary
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $start_at
 * @property \Illuminate\Support\Carbon|null $end_at
 * @property \Illuminate\Support\Carbon|null $fetched_at
 * @property \Illuminate\Support\Carbon|null $last_fetched_at
 * @property string|null $source_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Discounts\ModelHasOfferComponent> $invoiceTransactions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Discounts\ModelHasOfferComponent> $modelHasOfferComponents
 * @property-read \App\Models\Discounts\Offer $offer
 * @property-read \App\Models\Discounts\OfferCampaign $offerCampaign
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Discounts\ModelHasOfferComponent> $orderTransactions
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Catalogue\Shop $shop
 * @property-read \App\Models\Discounts\OfferComponentStats|null $stats
 * @method static \Database\Factories\Discounts\OfferComponentFactory factory($count = null, $state = [])
 * @method static Builder<static>|OfferComponent newModelQuery()
 * @method static Builder<static>|OfferComponent newQuery()
 * @method static Builder<static>|OfferComponent onlyTrashed()
 * @method static Builder<static>|OfferComponent query()
 * @method static Builder<static>|OfferComponent withTrashed()
 * @method static Builder<static>|OfferComponent withoutTrashed()
 * @mixin Eloquent
 */
class OfferComponent extends Model implements Auditable
{
    use SoftDeletes;
    use HasSlug;
    use HasFactory;
    use HasHistory;
    use InShop;

    protected $casts = [
        'data'  => 'array',
        'state' => OfferComponentStateEnum::class,
        'begin_at'        => 'datetime',
        'end_at'          => 'datetime',
        'fetched_at'      => 'datetime',
        'last_fetched_at' => 'datetime',
    ];

    protected $attributes = [
        'data' => '{}'
    ];

    protected $guarded = [];

    public function generateTags(): array
    {
        return ['discounts'];
    }

    protected array $auditInclude = [
        'code',
        'name',
        'state',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(6);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function offerCampaign(): BelongsTo
    {
        return $this->belongsTo(OfferCampaign::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(OfferComponentStats::class);
    }

    public function modelHasOfferComponents(): HasMany
    {
        return $this->hasMany(ModelHasOfferComponent::class);
    }

    public function invoiceTransactions(): HasMany
    {
        return $this->hasMany(ModelHasOfferComponent::class)
                    ->where('model_type', 'InvoiceTransaction');
    }

    public function orderTransactions(): HasMany
    {
        return $this->hasMany(ModelHasOfferComponent::class)
                    ->where('model_type', 'Transaction');
    }

}
