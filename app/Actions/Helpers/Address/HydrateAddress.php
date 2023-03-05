<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Feb 2023 13:01:38 Malaysia Time, Ubud
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Address;

use App\Actions\HydrateModel;
use App\Models\Helpers\Address;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HydrateAddress extends HydrateModel
{
    public string $commandSignature = 'hydrate:address {tenants?*} {--i|id=}';


    public function handle(Address $address): void
    {
        $usage = DB::table('addressables')->where('address_id', $address->id)->count();
        $address->update(['usage' => $usage]);
    }

    protected function getModel(int $id): Address
    {
        return Address::find($id);
    }

    protected function getAllModels(): Collection
    {
        return Address::get();
    }
}
