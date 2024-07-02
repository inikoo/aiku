<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Jan 2024 12:27:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use App\Enums\Mail\EmailTemplate\EmailTemplateStateEnum;
use App\Models\Helpers\Deployment;
use App\Models\Helpers\Media;
use App\Models\Helpers\Snapshot;
use App\Models\Inventory\Warehouse;
use App\Models\Traits\InShop;
use App\Models\Web\Website;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * App\Models\Mail\EmailTemplate
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string|null $type
 * @property array|null $data
 * @property int|null $screenshot_id
 * @property int $outbox_id
 * @property EmailTemplateStateEnum $state
 * @property int|null $unpublished_snapshot_id
 * @property int|null $live_snapshot_id
 * @property array $published_layout
 * @property \Illuminate\Support\Carbon|null $live_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Deployment> $deployments
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read Snapshot|null $liveSnapshot
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Mail\Outbox $outbox
 * @property-read \App\Models\Mail\Outbox|null $outboxes
 * @property-read Model|\Eloquent $parent
 * @property-read Media|null $screenshot
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Snapshot> $snapshots
 * @property-read Snapshot|null $unpublishedSnapshot
 * @property-read Warehouse|null $warehouse
 * @property-read Website|null $website
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate query()
 * @mixin \Eloquent
 */
class EmailTemplate extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use InShop;

    protected $guarded = [];

    protected $casts = [
        'data'             => 'array',
        'published_layout' => 'array',
        'state'            => EmailTemplateStateEnum::class,
        'live_at'          => 'datetime',
    ];

    protected $attributes = [
        'data'             => '{}',
        'published_layout' => '{}',
    ];


    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    public function snapshots(): MorphMany
    {
        return $this->morphMany(Snapshot::class, 'parent');
    }

    public function unpublishedSnapshot(): BelongsTo
    {
        return $this->belongsTo(Snapshot::class, 'unpublished_snapshot_id');
    }

    public function liveSnapshot(): BelongsTo
    {
        return $this->belongsTo(Snapshot::class, 'live_snapshot_id');
    }

    public function screenshot(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'screenshot_id');
    }

    public function deployments(): MorphMany
    {
        return $this->morphMany(Deployment::class, 'model');
    }

    public function outbox(): BelongsTo
    {
        return $this->belongsTo(Outbox::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
