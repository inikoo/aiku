<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 May 2024 15:25:34 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Address\Hydrators;

use App\Models\Helpers\Address;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class AddressHydrateFixedUsage implements ShouldBeUnique
{
    use AsAction;

    private Address $address;

    public function __construct(Address $address)
    {
        $this->address = $address;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->address->id))->dontRelease()];
    }

    public function handle(Address $address): void
    {
        if ($address->is_fixed) {
            $fixedUsage = DB::table('model_has_fixed_addresses')->where('group_id', $address->group_id)->where('address_id', $address->id)->count();
            $address->update(['fixed_usage' => $fixedUsage]);
        }
    }


}
