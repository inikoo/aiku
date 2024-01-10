<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 08 Oct 2023 21:39:19 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Helpers\Deployment
 *
 * @property int $id
 * @property string|null $slug
 * @property string $model_type
 * @property int $model_id
 * @property string|null $scope
 * @property string|null $publisher_type
 * @property int|null $publisher_id
 * @property int|null $snapshot_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $model
 * @property-read \App\Models\Helpers\Snapshot|null $snapshot
 * @method static \Illuminate\Database\Eloquent\Builder|Deployment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Deployment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Deployment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Deployment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deployment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deployment whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deployment whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deployment wherePublisherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deployment wherePublisherType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deployment whereScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deployment whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deployment whereSnapshotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deployment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Deployment extends Model
{
    use HasSlug;

    protected $dateFormat  = 'Y-m-d H:i:s P';
    protected array $dates = ['published_at'];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {

                return $this->snapshot->slug.'-'.now()->isoFormat('YYMMDD');
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnCreate()
            ->doNotGenerateSlugsOnUpdate();
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(Snapshot::class);
    }

}
