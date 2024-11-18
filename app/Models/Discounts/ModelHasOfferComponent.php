<?php
/*
 * author Arya Permana - Kirin
 * created on 18-11-2024-11h-08m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Discounts;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 *
 * @property int $id
 * @property int $model_id
 * @property string $model_type
 * @property int $offer_component_id
 * @property int $offer_id
 * @property int $offer_campaign_id
 * @property string|null $fetched_at
 * @property string|null $source_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $model
 * @property-read \App\Models\Discounts\Offer $offer
 * @property-read \App\Models\Discounts\OfferCampaign $offerCampaign
 * @property-read \App\Models\Discounts\OfferComponent $offerComponent
 * @method static Builder<static>|ModelHasOfferComponent newModelQuery()
 * @method static Builder<static>|ModelHasOfferComponent newQuery()
 * @method static Builder<static>|ModelHasOfferComponent onlyTrashed()
 * @method static Builder<static>|ModelHasOfferComponent query()
 * @method static Builder<static>|ModelHasOfferComponent withTrashed()
 * @method static Builder<static>|ModelHasOfferComponent withoutTrashed()
 * @mixin Eloquent
 */
class ModelHasOfferComponent extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function offerComponent(): BelongsTo
    {
        return $this->belongsTo(OfferComponent::class);
    }

    public function offerCampaign(): BelongsTo
    {
        return $this->belongsTo(OfferCampaign::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

}
