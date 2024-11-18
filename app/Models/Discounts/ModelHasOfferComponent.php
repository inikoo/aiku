<?php
/*
 * author Arya Permana - Kirin
 * created on 18-11-2024-11h-08m
 * github: https://github.com/KirinZero0
 * copyright 2024
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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class ModelHasOfferComponent extends Model
{
    use SoftDeletes;
    
    protected $guarded = [];

    public function model():MorphTo
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
