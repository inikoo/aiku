<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 14:10:34 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\HumanResources;

use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateEmployees;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Enums\HumanResources\Employee\EmployeeTypeEnum;
use App\Enums\Miscellaneous\GenderEnum;
use App\Models\Auth\User;
use App\Models\Helpers\Issue;
use App\Models\Media\GroupMedia;
use App\Models\Search\UniversalSearch;
use App\Models\Traits\HasPhoto;
use App\Models\Traits\HasUniversalSearch;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\HumanResources\Employee
 *
 * @property int $id
 * @property string $slug
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
 * @property array $job_position_scopes
 * @property array $errors
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read Collection<int, Issue> $issues
 * @property-read Collection<int, \App\Models\HumanResources\JobPosition> $jobPositions
 * @property-read MediaCollection<int, GroupMedia> $media
 * @property-read UniversalSearch|null $universalSearch
 * @property-read User|null $user
 * @method static \Database\Factories\HumanResources\EmployeeFactory factory($count = null, $state = [])
 * @method static Builder|Employee newModelQuery()
 * @method static Builder|Employee newQuery()
 * @method static Builder|Employee onlyTrashed()
 * @method static Builder|Employee query()
 * @method static Builder|Employee withTrashed()
 * @method static Builder|Employee withoutTrashed()
 * @mixin Eloquent
 */
class Employee extends Model implements HasMedia
{
    use UsesTenantConnection;
    use HasSlug;
    use SoftDeletes;
    use HasUniversalSearch;
    use HasPhoto;
    use HasFactory;

    protected $casts = [
        'data'                => 'array',
        'errors'              => 'array',
        'salary'              => 'array',
        'working_hours'       => 'array',
        'job_position_scopes' => 'array',
        'date_of_birth'       => 'datetime:Y-m-d',
        'gender'              => GenderEnum::class,
        'state'               => EmployeeStateEnum::class,
        'type'                => EmployeeTypeEnum::class

    ];

    protected $attributes = [
        'data'                => '{}',
        'errors'              => '{}',
        'salary'              => '{}',
        'working_hours'       => '{}',
        'job_position_scopes' => '{}',
    ];


    protected $guarded = [];


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


    protected static function booted(): void
    {
        static::updated(function (Employee $employee) {
            if (!$employee->wasRecentlyCreated) {
                if ($employee->wasChanged('state')) {
                    TenantHydrateEmployees::dispatch(app('currentTenant'));
                }
            }
        });
    }

    public function jobPositions(): BelongsToMany
    {
        return $this->belongsToMany(JobPosition::class)
            ->using(EmployeeJobPosition::class)
            ->withTimestamps()
            ->withPivot('share');
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

}
