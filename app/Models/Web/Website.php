<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 11:32:29 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use App\Enums\Web\Website\WebsiteCloudflareStatusEnum;
use App\Enums\Web\Website\WebsiteEngineEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Enums\Web\Website\WebsiteTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Helpers\Deployment;
use App\Models\Helpers\Media;
use App\Models\Helpers\Snapshot;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InShop;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
 * App\Models\Web\Website
 *
 * @property int $id
 * @property string $slug
 * @property int $group_id
 * @property int $organisation_id
 * @property int $shop_id
 * @property WebsiteTypeEnum $type
 * @property WebsiteEngineEnum $engine
 * @property string $code
 * @property string $name
 * @property WebsiteStateEnum $state
 * @property bool $status
 * @property string $domain
 * @property array $settings
 * @property array $data
 * @property array $structure
 * @property array $layout
 * @property array $published_layout
 * @property int|null $unpublished_header_snapshot_id
 * @property int|null $live_header_snapshot_id
 * @property string|null $published_header_checksum
 * @property bool $header_is_dirty
 * @property int|null $unpublished_footer_snapshot_id
 * @property int|null $live_footer_snapshot_id
 * @property string|null $published_footer_checksum
 * @property bool $footer_is_dirty
 * @property int|null $current_layout_id
 * @property int|null $logo_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $launched_at
 * @property Carbon|null $closed_at
 * @property Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property string|null $cloudflare_id
 * @property WebsiteCloudflareStatusEnum|null $cloudflare_status
 * @property string|null $source_id
 * @property int|null $storefront_id
 * @property-read Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read mixed $condition
 * @property-read Collection<int, Deployment> $deployments
 * @property-read Group $group
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $images
 * @property-read Snapshot|null $liveSnapshot
 * @property-read Organisation $organisation
 * @property-read Shop $shop
 * @property-read Collection<int, Snapshot> $snapshots
 * @property-read \App\Models\Web\Webpage|null $storefront
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
 * @property-read Snapshot|null $unpublishedFooterSnapshot
 * @property-read Snapshot|null $unpublishedHeaderSnapshot
 * @property-read \App\Models\Web\WebsiteStats|null $webStats
 * @property-read Collection<int, \App\Models\Web\Webpage> $webpages
 * @method static \Database\Factories\Web\WebsiteFactory factory($count = null, $state = [])
 * @method static Builder|Website newModelQuery()
 * @method static Builder|Website newQuery()
 * @method static Builder|Website onlyTrashed()
 * @method static Builder|Website query()
 * @method static Builder|Website withTrashed()
 * @method static Builder|Website withoutTrashed()
 * @mixin Eloquent
 */
class Website extends Model implements Auditable
{
    use HasSlug;
    use SoftDeletes;
    use HasHistory;
    use HasUniversalSearch;
    use HasFactory;
    use InShop;

    protected $casts = [
        'type'               => WebsiteTypeEnum::class,
        'data'               => 'array',
        'settings'           => 'array',
        'structure'          => 'array',
        'layout'             => 'array',
        'published_layout'   => 'array',
        'state'              => WebsiteStateEnum::class,
        'status'             => 'boolean',
        'engine'             => WebsiteEngineEnum::class,
        'cloudflare_status'  => WebsiteCloudflareStatusEnum::class,
        'launched_at'        => 'datetime',
        'closed_at'          => 'datetime',

    ];

    protected $attributes = [
        'data'             => '{}',
        'settings'         => '{}',
        'structure'        => '{}',
        'layout'           => '{}',
        'published_layout' => '{}',
    ];

    protected $guarded = [];

    public function generateTags(): array
    {
        return [
            'websites'
        ];
    }

    protected array $auditExclude = [
        'id',
        'slug',
        'storefront_id',
        'live_header_snapshot_id',
        'live_footer_snapshot_id',
        'storefront_id',
        'published_layout',
        'unpublished_header_snapshot_id',
        'unpublished_footer_snapshot_id',
        'published_header_checksum',
        'published_footer_checksum'
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }


    public function webpages(): HasMany
    {
        return $this->hasMany(Webpage::class);
    }

    public function webStats(): HasOne
    {
        return $this->hasOne(WebsiteStats::class);
    }

    public function storefront(): BelongsTo
    {
        return $this->belongsTo(Webpage::class, 'storefront_id');
    }

    protected function condition(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if ($attributes['state'] == 'live') {
                    return $attributes['status'] ? 'live' : 'maintenance';
                }

                return $attributes['state'];
            }
        );
    }

    public function images(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'model', 'model_has_media');
    }

    public function snapshots(): MorphMany
    {
        return $this->morphMany(Snapshot::class, 'parent');
    }

    public function unpublishedHeaderSnapshot(): BelongsTo
    {
        return $this->belongsTo(Snapshot::class, 'unpublished_header_snapshot_id');
    }

    public function unpublishedFooterSnapshot(): BelongsTo
    {
        return $this->belongsTo(Snapshot::class, 'unpublished_footer_snapshot_id');
    }

    public function liveSnapshot(): BelongsTo
    {
        return $this->belongsTo(Snapshot::class, 'live_snapshot_id');
    }

    public function deployments(): MorphMany
    {
        return $this->morphMany(Deployment::class, 'model');
    }

    public function getUrl(): string
    {
        $scheme = app()->environment('production') ? 'https' : 'http';

        return $scheme.'://'.$this->domain;
    }


}
