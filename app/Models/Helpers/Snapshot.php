<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Oct 2023 09:32:00 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use App\Enums\Helpers\Snapshot\SnapshotScopeEnum;
use App\Enums\Helpers\Snapshot\SnapshotStateEnum;
use App\Http\Resources\Web\SlideResource;
use App\Models\Web\Slide;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;

/**
 * App\Models\Helpers\Snapshot
 *
 * @property int $id
 * @property SnapshotScopeEnum $scope
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
 * @property-read \App\Models\Helpers\SnapshotStats|null $stats
 * @method static Builder|Snapshot newModelQuery()
 * @method static Builder|Snapshot newQuery()
 * @method static Builder|Snapshot query()
 * @mixin \Eloquent
 */
class Snapshot extends Model
{
    protected $dateFormat  = 'Y-m-d H:i:s P';
    protected array $dates = ['published_at', 'published_until'];

    protected $casts = [
        'layout' => 'array',
        'state'  => SnapshotStateEnum::class,
        'scope'  => SnapshotScopeEnum::class
    ];

    protected $attributes = [
        'layout' => '{}'
    ];

    protected $guarded = [];

    public function generateTags(): array
    {
        return [
            'helpers',
        ];
    }

    protected array $auditInclude = [
        'code',
        'name',
        'description',
        'status',
        'state',
        'price',
        'currency_id',
        'units',
        'unit',
        'barcode',
        'rrp',
        'unit_relationship_type'
    ];

    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    public function publisher(): MorphTo
    {
        return $this->morphTo();
    }

    public function stats(): hasOne
    {
        return $this->hasOne(SnapshotStats::class);
    }

    public function slides(): HasMany
    {
        return $this->hasMany(Slide::class);
    }

    public function compiledLayout(): array|string
    {
        switch (class_basename($this->parent)) {
            case 'Banner':
                $slides         = $this->slides()->where('visibility', true)->get();
                $compiledLayout = $this->layout;
                data_set($compiledLayout, 'components', json_decode(SlideResource::collection($slides)->toJson(), true));
                data_set($compiledLayout, 'type', $this->parent->type);

                return $compiledLayout;
            case 'Website':
            case 'Webpage':
                return Arr::get($this->layout, 'html');
            default:
                return [];
        }
    }
}
