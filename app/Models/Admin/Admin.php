<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 14 Aug 2022 20:12:49 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Models\Admin;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;



/**
 * App\Models\Admin\Admin
 *
 * @property-read \App\Models\Admin\AdminUser|null $adminUser
 * @method static Builder|Admin newModelQuery()
 * @method static Builder|Admin newQuery()
 * @method static Builder|Admin query()
 * @mixin \Eloquent
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $email
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Admin whereCreatedAt($value)
 * @method static Builder|Admin whereData($value)
 * @method static Builder|Admin whereEmail($value)
 * @method static Builder|Admin whereId($value)
 * @method static Builder|Admin whereName($value)
 * @method static Builder|Admin whereSlug($value)
 * @method static Builder|Admin whereUpdatedAt($value)
 */
class Admin extends Model
{


    protected $guarded = [];
    protected $attributes = [
        'data' => '{}',
    ];
    protected $casts = [
        'data' => 'array'
    ];


    public function adminUser(): HasOne
    {
        return $this->hasOne(AdminUser::class);
    }

}
