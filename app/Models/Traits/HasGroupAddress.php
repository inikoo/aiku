<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 14 May 2023 00:47:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Traits;

use App\Models\Helpers\GroupAddress;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasGroupAddress
{
    use HasAddress;

    public function addresses(): MorphToMany
    {
        return $this->morphToMany(GroupAddress::class, 'group_addressable')->withTimestamps();
    }

    public function getAddress(string $scope = 'contact'): ?GroupAddress
    {
        return $this->addresses()->where('scope', '=', $scope)->first();
    }

}
