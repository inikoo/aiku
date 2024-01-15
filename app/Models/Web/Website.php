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
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
 * @property string $type
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
 * @property array $compiled_layout
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
 * @property string|null $launched_at
 * @property string|null $closed_at
 * @property Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property string|null $cloudflare_id
 * @property WebsiteCloudflareStatusEnum|null $cloudflare_status
 * @property string|null $source_id
 * @property-read Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read Organisation $organisation
 * @property-read Shop $shop
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
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

    protected $casts = [
        'data'               => 'array',
        'settings'           => 'array',
        'structure'          => 'array',
        'layout'             => 'array',
        'compiled_layout'    => 'array',
        'state'              => WebsiteStateEnum::class,
        'engine'             => WebsiteEngineEnum::class,
        'cloudflare_status'  => WebsiteCloudflareStatusEnum::class
    ];

    protected $attributes = [
        'data'            => '{}',
        'settings'        => '{}',
        'structure'       => '{}',
        'layout'          => '{}',
        'compiled_layout' => '{}',
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
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function webpages(): HasMany
    {
        return $this->hasMany(Webpage::class);
    }

    public function webStats(): HasOne
    {
        return $this->hasOne(WebsiteStats::class);
    }
}
