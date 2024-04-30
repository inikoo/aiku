<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:30:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use App\Models\HumanResources\Clocking;
use App\Models\HumanResources\JobPosition;
use App\Models\HumanResources\Timesheet;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\SysAdmin\Guest
 *
 * @property int $id
 * @property int $group_id
 * @property string $slug
 * @property string $alias
 * @property bool $status
 * @property string|null $contact_name
 * @property string|null $company_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $identity_document_type
 * @property string|null $identity_document_number
 * @property Carbon|null $date_of_birth
 * @property string|null $gender
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property string|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Clocking> $clockings
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, JobPosition> $jobPositions
 * @property-read MediaCollection<int, \App\Models\Media\Media> $media
 * @property-read \App\Models\SysAdmin\GuestStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Timesheet> $timesheets
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @property-read \App\Models\SysAdmin\User|null $user
 * @method static \Database\Factories\SysAdmin\GuestFactory factory($count = null, $state = [])
 * @method static Builder|Guest newModelQuery()
 * @method static Builder|Guest newQuery()
 * @method static Builder|Guest onlyTrashed()
 * @method static Builder|Guest query()
 * @method static Builder|Guest withTrashed()
 * @method static Builder|Guest withoutTrashed()
 * @mixin Eloquent
 */
class Guest extends Model implements HasMedia, Auditable
{
    use HasSlug;
    use InteractsWithMedia;
    use SoftDeletes;
    use HasFactory;
    use HasHistory;
    use HasUniversalSearch;


    protected $casts = [
        'data'          => 'array',
        'date_of_birth' => 'datetime:Y-m-d',
        'status'        => 'boolean',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];


    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('alias')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(16)
            ->doNotGenerateSlugsOnUpdate();
    }

    public function generateTags(): array
    {
        return [
            'sysadmin'
        ];
    }


    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'parent');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')
            ->singleFile()
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->width(256)
                    ->height(256);
            });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function jobPositions(): MorphToMany
    {
        return $this->morphToMany(JobPosition::class, 'job_positionable');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function getGroup(): Group
    {
        return $this->group;
    }

    public function getGroupId(): int
    {
        return $this->group_id;
    }

    public function stats(): HasOne
    {
        return $this->hasOne(GuestStats::class);
    }

    public function timesheets(): MorphMany
    {
        return $this->morphMany(Timesheet::class, 'subject');
    }

    public function clockings(): MorphMany
    {
        return $this->morphMany(Clocking::class, 'subject');
    }

}
