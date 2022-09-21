<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 17:21:07 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;


/**
 * App\Models\SysAdmin\AdminUser
 *
 * @property-read \App\Models\SysAdmin\Admin|null $admin
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static Builder|AdminUser newModelQuery()
 * @method static Builder|AdminUser newQuery()
 * @method static \Illuminate\Database\Query\Builder|AdminUser onlyTrashed()
 * @method static Builder|AdminUser query()
 * @method static \Illuminate\Database\Query\Builder|AdminUser withTrashed()
 * @method static \Illuminate\Database\Query\Builder|AdminUser withoutTrashed()
 * @mixin \Eloquent
 */
class AdminUser extends Authenticatable
{
    use HasFactory;
    use HasApiTokens;
    use SoftDeletes;

    protected $casts = [
        'data'     => 'array',
        'settings' => 'array',
        'status'   => 'boolean'
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * @return string
     * Hack for laravel permissions to work
     */
    public function guardName(): string
    {
        return 'admin';
    }

    protected $guarded = [];


    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }


}
