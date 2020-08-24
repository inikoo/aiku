<?php
/*
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App\Models\HR;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 * App\Models\HR\ClockingMachine
 *
 * @method static Builder|ClockingMachine findSimilarSlugs($attribute, $config, $slug)
 * @method static Builder|ClockingMachine newModelQuery()
 * @method static Builder|ClockingMachine newQuery()
 * @method static Builder|ClockingMachine query()
 * @mixin \Eloquent
 */
class ClockingMachine extends Model {

    use UsesTenantConnection, Sluggable;

    public function sluggable() {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}'
    ];

    protected $fillable = ['name'];
}
