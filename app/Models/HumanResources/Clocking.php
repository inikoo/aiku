<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 09 Jun 2023 03:43:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\HumanResources;

use App\Actions\Helpers\Images\GetPictureSources;
use App\Enums\HumanResources\Clocking\ClockingTypeEnum;
use App\Models\Helpers\Media;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 *
 *
 * @property int $id
 * @property int|null $workplace_id
 * @property int|null $timesheet_id
 * @property ClockingTypeEnum $type
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property int|null $time_tracker_id
 * @property int|null $clocking_machine_id
 * @property Carbon $clocked_at
 * @property string|null $generator_type
 * @property int|null $generator_id
 * @property string|null $notes
 * @property int|null $image_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $deleted_by_type
 * @property int|null $deleted_by_id
 * @property string|null $source_id
 * @property-read \App\Models\HumanResources\ClockingMachine|null $clockingMachine
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read Media|null $photo
 * @property-read Model|\Eloquent|null $subject
 * @property-read \App\Models\HumanResources\Timesheet|null $timesheet
 * @property-read \App\Models\HumanResources\Workplace|null $workplace
 * @method static Builder|Clocking newModelQuery()
 * @method static Builder|Clocking newQuery()
 * @method static Builder|Clocking onlyTrashed()
 * @method static Builder|Clocking query()
 * @method static Builder|Clocking withTrashed()
 * @method static Builder|Clocking withoutTrashed()
 * @mixin Eloquent
 */
class Clocking extends Model implements HasMedia
{
    use SoftDeletes;
    use InteractsWithMedia;

    protected $casts = [
        'clocked_at' => 'datetime:Y-m-d H:i:s',
        'type'       => ClockingTypeEnum::class
    ];


    protected $guarded = [];

    public function subject(): BelongsTo
    {
        return $this->morphTo();
    }

    public function workplace(): BelongsTo
    {
        return $this->belongsTo(Workplace::class);
    }

    public function clockingMachine(): BelongsTo
    {
        return $this->belongsTo(ClockingMachine::class);
    }

    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')
            ->singleFile();
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function photoImageSources($width = 0, $height = 0)
    {
        if($this->photo) {
            $photoThumbnail = $this->photo->getImage()->resize($width, $height);
            return GetPictureSources::run($photoThumbnail);
        }
        return null;
    }

}
