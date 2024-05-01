<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Apr 2024 12:33:43 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\HumanResources;

use App\Models\SysAdmin\Guest;
use App\Models\Traits\InOrganisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property \Illuminate\Support\Carbon $date
 * @property string $subject_type Employee|Guest
 * @property int $subject_id
 * @property \Illuminate\Support\Carbon|null $start_at
 * @property \Illuminate\Support\Carbon|null $end_at
 * @property int $number_time_trackers
 * @property int $number_open_time_trackers
 * @property int $working_duration seconds
 * @property int $breaks_duration seconds
 * @property int $total_duration seconds
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $source_id
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read Model|\Eloquent $subject
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HumanResources\TimeTracker> $timeTrackers
 * @method static \Illuminate\Database\Eloquent\Builder|Timesheet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Timesheet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Timesheet query()
 * @mixin \Eloquent
 */
class Timesheet extends Model
{
    use InOrganisation;
    use HasSlug;


    protected $casts = [
        'date'     => 'date',
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                if ($this->subject_type == 'Employee') {
                    $subject = Employee::find($this->subject_id);
                } else {
                    $subject = Guest::find($this->subject_id);
                }

                return $this->date->format('Y-m-d').' '.$subject->slug;
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(64);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function timeTrackers(): HasMany
    {
        return $this->hasMany(TimeTracker::class);
    }

}
