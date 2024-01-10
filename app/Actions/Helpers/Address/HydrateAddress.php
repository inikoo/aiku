<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Feb 2023 13:01:38 Malaysia Time, Ubud
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Address;

use App\Actions\Helpers\Address\Hydrators\AddressHydrateUsage;
use App\Actions\HydrateModel;
use App\Models\Helpers\Address;
use Illuminate\Support\Collection;

class HydrateAddress extends HydrateModel
{
    public string $commandSignature = 'hydrate:address {organisations?*} {--i|id=}';


    public function handle(Address $address): void
    {
        AddressHydrateUsage::run($address);
    }

    protected function getModel(string $slug): Address
    {
        return Address::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Address::get();
    }
}
