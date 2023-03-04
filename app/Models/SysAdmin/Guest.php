<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 13 Sept 2022 12:56:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use App\Actions\Central\Tenant\HydrateTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;


/**
 * App\Models\SysAdmin\Guest
 *
 * @property int $id
 * @property string $slug
 * @property bool $status
 * @property string $type
 * @property string|null $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $identity_document_type
 * @property string|null $identity_document_number
 * @property \Illuminate\Support\Carbon|null $date_of_birth
 * @property string|null $gender
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\SysAdmin\User|null $user
 * @method static Builder|Guest newModelQuery()
 * @method static Builder|Guest newQuery()
 * @method static Builder|Guest onlyTrashed()
 * @method static Builder|Guest query()
 * @method static Builder|Guest whereCreatedAt($value)
 * @method static Builder|Guest whereData($value)
 * @method static Builder|Guest whereDateOfBirth($value)
 * @method static Builder|Guest whereDeletedAt($value)
 * @method static Builder|Guest whereEmail($value)
 * @method static Builder|Guest whereGender($value)
 * @method static Builder|Guest whereId($value)
 * @method static Builder|Guest whereIdentityDocumentNumber($value)
 * @method static Builder|Guest whereIdentityDocumentType($value)
 * @method static Builder|Guest whereName($value)
 * @method static Builder|Guest wherePhone($value)
 * @method static Builder|Guest whereSlug($value)
 * @method static Builder|Guest whereSourceId($value)
 * @method static Builder|Guest whereStatus($value)
 * @method static Builder|Guest whereType($value)
 * @method static Builder|Guest whereUpdatedAt($value)
 * @method static Builder|Guest withTrashed()
 * @method static Builder|Guest withoutTrashed()
 * @mixin \Eloquent
 */
class Guest extends Model implements HasMedia
{
    use HasSlug;
    use InteractsWithMedia;
    use SoftDeletes;
    use UsesTenantConnection;

    protected $casts = [
        'data'          => 'array',
        'date_of_birth' => 'datetime:Y-m-d',
        'status'        => 'boolean'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];


    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return head(explode(' ', trim($this->name)));
            })
            ->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(16);
    }

    protected static function booted()
    {
        static::created(
            function () {
                HydrateTenant::make()->guestsStats();
            }
        );
        static::deleted(
            function () {
                HydrateTenant::make()->guestsStats();
            }
        );
        static::updated(function (Guest $guest) {
            if (!$guest->wasRecentlyCreated) {
                if ($guest->wasChanged('status')) {
                    HydrateTenant::make()->guestsStats();
                    if (!$guest->status) {
                        $guest->user->update(
                            [
                                'status' => $guest->status
                            ]
                        );
                    }
                }
                if ($guest->wasChanged('name')) {
                    $guest->user->update(
                        [
                            'name' => $guest->name
                        ]
                    );
                }
            }
        });
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

}
