<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 14:10:34 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\HumanResources;

use App\Audits\Redactors\EmployeePinRedactor;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Enums\HumanResources\Employee\EmployeeTypeEnum;
use App\Enums\Miscellaneous\GenderEnum;
use App\Models\Helpers\Issue;
use App\Models\Helpers\UniversalSearch;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\Task;
use App\Models\SysAdmin\User;
use App\Models\Traits\HasAttachments;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasImage;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InOrganisation;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\HumanResources\Employee
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property string $alias
 * @property string|null $work_email
 * @property string|null $contact_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $identity_document_type
 * @property string|null $identity_document_number
 * @property Carbon|null $date_of_birth
 * @property GenderEnum|null $gender
 * @property string|null $worker_number
 * @property string|null $job_title
 * @property EmployeeTypeEnum $type
 * @property EmployeeStateEnum $state
 * @property string|null $employment_start_at
 * @property string|null $employment_end_at
 * @property string|null $emergency_contact
 * @property array|null $salary
 * @property array|null $working_hours
 * @property string $week_working_hours
 * @property array $data
 * @property array $errors
 * @property string|null $pin
 * @property int|null $image_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property string|null $source_id
 * @property-read MediaCollection<int, \App\Models\Helpers\Media> $attachments
 * @property-read Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read Collection<int, \App\Models\HumanResources\Clocking> $clockings
 * @property-read Group $group
 * @property-read \App\Models\Helpers\Media|null $image
 * @property-read MediaCollection<int, \App\Models\Helpers\Media> $images
 * @property-read Collection<int, Issue> $issues
 * @property-read \App\Models\HumanResources\JobPositionable $pivot
 * @property-read Collection<int, \App\Models\HumanResources\JobPosition> $jobPositions
 * @property-read MediaCollection<int, \App\Models\Helpers\Media> $media
 * @property-read Organisation $organisation
 * @property-read \App\Models\HumanResources\EmployeeStats|null $stats
 * @property-read Collection<int, Task> $tasks
 * @property-read Collection<int, \App\Models\HumanResources\TimeTracker> $timeTrackers
 * @property-read Collection<int, \App\Models\HumanResources\Timesheet> $timesheets
 * @property-read UniversalSearch|null $universalSearch
 * @property-read User|null $user
 * @property-read Collection<int, \App\Models\HumanResources\Workplace> $workplaces
 * @method static \Database\Factories\HumanResources\EmployeeFactory factory($count = null, $state = [])
 * @method static Builder|Employee newModelQuery()
 * @method static Builder|Employee newQuery()
 * @method static Builder|Employee onlyTrashed()
 * @method static Builder|Employee query()
 * @method static Builder|Employee withTrashed()
 * @method static Builder|Employee withoutTrashed()
 * @mixin Eloquent
 */
class Employee extends Model implements HasMedia, Auditable
{
    use HasSlug;
    use SoftDeletes;
    use HasUniversalSearch;
    use HasImage;
    use HasAttachments;
    use HasFactory;
    use HasHistory;
    use InOrganisation;

    protected $casts = [
        'week_working_hours' => 'decimal:2',
        'data'               => 'array',
        'errors'             => 'array',
        'salary'             => 'array',
        'working_hours'      => 'array',
        'date_of_birth'      => 'datetime:Y-m-d',
        'gender'             => GenderEnum::class,
        'state'              => EmployeeStateEnum::class,
        'type'               => EmployeeTypeEnum::class

    ];

    protected $attributes = [
        'data'          => '{}',
        'errors'        => '{}',
        'salary'        => '{}',
        'working_hours' => '{}',
    ];

    protected $guarded = [];

    public function generateTags(): array
    {
        return ['hr'];
    }

    protected array $auditInclude = [
        'alias',
        'work_email',
        'contact_name',
        'email',
        'phone',
        'identity_document_type',
        'identity_document_number',
        'date_of_birth',
        'gender',
        'worker_number',
        'job_title',
        'type',
        'state',
        'employment_start_at',
        'employment_end_at',
        'emergency_contact',
        'pin'
    ];

    protected array $attributeModifiers = [
        'pin' => EmployeePinRedactor::class,
    ];


    public static function boot(): void
    {
        parent::boot();

        if (app('app.scope') == 'han') {
            static::addGlobalScope('han', function ($builder) {
                /** @var ClockingMachine $clockingMachine */
                $clockingMachine = Auth::user();
                $builder->where('organisation_id', $clockingMachine->organisation_id);
            });
        }
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return head(explode(' ', trim($this->contact_name)));
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(16);
    }

    public function jobPositions(): MorphToMany
    {
        return $this->morphToMany(JobPosition::class, 'job_positionable')->using(JobPositionable::class)
            ->withPivot(['share', 'scopes'])
            ->withTimestamps();
    }

    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'parent');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function issues(): MorphToMany
    {
        return $this->morphToMany(Issue::class, 'issuable');
    }

    public function workplaces(): BelongsToMany
    {
        return $this->belongsToMany(Workplace::class)->withTimestamps();
    }

    public function stats(): HasOne
    {
        return $this->hasOne(EmployeeStats::class);
    }

    public function timesheets(): MorphMany
    {
        return $this->morphMany(Timesheet::class, 'subject');
    }

    public function timeTrackers(): MorphMany
    {
        return $this->morphMany(TimeTracker::class, 'subject');
    }

    public function clockings(): MorphMany
    {
        return $this->morphMany(Clocking::class, 'subject');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'assigner');
    }


}
