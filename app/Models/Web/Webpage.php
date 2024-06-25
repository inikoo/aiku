<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 11:32:29 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use App\Enums\Web\Webpage\WebpagePurposeEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Models\Helpers\Deployment;
use App\Models\Helpers\Snapshot;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InShop;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Web\Webpage
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $shop_id
 * @property int|null $parent_id
 * @property int $website_id
 * @property string $slug
 * @property string $code
 * @property string $url
 * @property int $level
 * @property bool $is_fixed
 * @property WebpageStateEnum $state
 * @property WebpageTypeEnum $type
 * @property WebpagePurposeEnum $purpose
 * @property int|null $unpublished_snapshot_id
 * @property int|null $live_snapshot_id
 * @property array $published_layout
 * @property Carbon|null $ready_at
 * @property Carbon|null $live_at
 * @property Carbon|null $closed_at
 * @property string|null $published_checksum
 * @property bool $is_dirty
 * @property array $data
 * @property array $settings
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property string|null $source_id
 * @property-read Collection<int, Deployment> $deployments
 * @property-read Group $group
 * @property-read Organisation $organisation
 * @property-read Webpage|null $parent
 * @property-read \App\Models\Catalogue\Shop $shop
 * @property-read Collection<int, Snapshot> $snapshots
 * @property-read \App\Models\Web\WebpageStats|null $stats
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
 * @property-read Snapshot|null $unpublishedSnapshot
 * @property-read Collection<int, \App\Models\Web\WebBlock> $webBlocks
 * @property-read Collection<int, Webpage> $webpages
 * @property-read \App\Models\Web\Website $website
 * @method static \Database\Factories\Web\WebpageFactory factory($count = null, $state = [])
 * @method static Builder|Webpage newModelQuery()
 * @method static Builder|Webpage newQuery()
 * @method static Builder|Webpage onlyTrashed()
 * @method static Builder|Webpage query()
 * @method static Builder|Webpage withTrashed()
 * @method static Builder|Webpage withoutTrashed()
 * @mixin Eloquent
 */
class Webpage extends Model implements Auditable
{
    use HasSlug;
    use HasFactory;
    use HasUniversalSearch;
    use SoftDeletes;
    use InShop;
    use HasHistory;

    protected $casts = [
        'data'             => 'array',
        'settings'         => 'array',
        'published_layout' => 'array',
        'state'            => WebpageStateEnum::class,
        'purpose'          => WebpagePurposeEnum::class,
        'type'             => WebpageTypeEnum::class,
        'ready_at'         => 'datetime',
        'live_at'          => 'datetime',
        'closed_at'        => 'datetime'
    ];

    protected $attributes = [
        'data'             => '{}',
        'settings'         => '{}',
        'published_layout' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function generateTags(): array
    {
        return [
            'websites'
        ];
    }

    protected array $auditInclude = [
        'code',
        'url',
        'state',
        'ready_at',
        'live_at',
        'closed_at',
        'purpose',
        'type'
    ];

    public function stats(): HasOne
    {
        return $this->hasOne(WebpageStats::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }


    public function snapshots(): MorphMany
    {
        return $this->morphMany(Snapshot::class, 'parent');
    }

    public function unpublishedSnapshot(): BelongsTo
    {
        return $this->belongsTo(Snapshot::class, 'unpublished_snapshot_id');
    }

    public function deployments(): MorphMany
    {
        return $this->morphMany(Deployment::class, 'model');
    }

    public function webpages(): HasMany
    {
        return $this->hasMany(Webpage::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Webpage::class, 'parent_id');
    }

    public function webBlocks(): MorphToMany
    {
        return $this->morphToMany(WebBlock::class, 'model', 'model_has_web_blocks')
            ->orderByPivot('position')
            ->withPivot('id', 'position')
            ->withTimestamps();
    }

}
