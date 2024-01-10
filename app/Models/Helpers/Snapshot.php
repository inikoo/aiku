<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Oct 2023 09:32:00 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use App\Enums\Helpers\Snapshot\SnapshotStateEnum;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Helpers\Snapshot
 *
 * @property int $id
 * @property string|null $slug
 * @property string|null $scope
 * @property string|null $publisher_type
 * @property int|null $publisher_id
 * @property string|null $parent_type
 * @property int|null $parent_id
 * @property int|null $customer_id
 * @property SnapshotStateEnum $state
 * @property string|null $published_at
 * @property string|null $published_until
 * @property string $checksum
 * @property array $layout
 * @property string|null $comment
 * @property bool $first_commit
 * @property bool|null $recyclable
 * @property string|null $recyclable_tag
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $parent
 * @property-read Model|\Eloquent $publisher
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Slide> $slides
 * @property-read int|null $slides_count
 * @method static Builder|Snapshot newModelQuery()
 * @method static Builder|Snapshot newQuery()
 * @method static Builder|Snapshot query()
 * @method static Builder|Snapshot whereChecksum($value)
 * @method static Builder|Snapshot whereComment($value)
 * @method static Builder|Snapshot whereCreatedAt($value)
 * @method static Builder|Snapshot whereCustomerId($value)
 * @method static Builder|Snapshot whereFirstCommit($value)
 * @method static Builder|Snapshot whereId($value)
 * @method static Builder|Snapshot whereLayout($value)
 * @method static Builder|Snapshot whereParentId($value)
 * @method static Builder|Snapshot whereParentType($value)
 * @method static Builder|Snapshot wherePublishedAt($value)
 * @method static Builder|Snapshot wherePublishedUntil($value)
 * @method static Builder|Snapshot wherePublisherId($value)
 * @method static Builder|Snapshot wherePublisherType($value)
 * @method static Builder|Snapshot whereRecyclable($value)
 * @method static Builder|Snapshot whereRecyclableTag($value)
 * @method static Builder|Snapshot whereScope($value)
 * @method static Builder|Snapshot whereSlug($value)
 * @method static Builder|Snapshot whereState($value)
 * @method static Builder|Snapshot whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Snapshot extends Model
{
    use HasSlug;

    protected $dateFormat  = 'Y-m-d H:i:s P';
    protected array $dates = ['published_at', 'published_until'];

    protected $casts = [
        'layout' => 'array',
        'state'  => SnapshotStateEnum::class
    ];

    protected $attributes = [
        'layout' => '{}'
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                /** @var Webpage|Website $parent */
                $parent = $this->parent;
                $slug   = $parent->slug;
                if ($this->scope) {
                    $slug .= " $this->scope";
                }
                return $slug;
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnCreate()
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }


    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    public function publisher(): MorphTo
    {
        return $this->morphTo();
    }



    public function compiledLayout(): array|string
    {
        switch (class_basename($this->parent)) {
            case 'Website':
            case 'Webpage':
                return Arr::get($this->layout, 'html');
            default:
                return [];
        }
    }


}
