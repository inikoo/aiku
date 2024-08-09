<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Aug 2024 14:36:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 *
 *
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeHasOtherOrganisationJobPosition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeHasOtherOrganisationJobPosition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeHasOtherOrganisationJobPosition query()
 * @mixin \Eloquent
 */
class EmployeeHasOtherOrganisationJobPosition extends Pivot
{
    protected $guarded = [];

    protected $casts = [
        'scopes' => 'array',
    ];

    protected $attributes = [
        'scopes' => '{}',
    ];


}
