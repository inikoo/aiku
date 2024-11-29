<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 28 Nov 2024 21:11:58 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithFixedAddressActions;
use App\Actions\Traits\WithModelAddressActions;
use App\Models\Helpers\Address;
use App\Models\SysAdmin\Organisation;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;

class StoreOrganisationAddress extends OrgAction
{
    use WithActionUpdate;
    use WithFixedAddressActions;
    use WithModelAddressActions;


    public function handle(Organisation $organisation, array $modelData): Organisation
    {

        data_set($addressData, 'group_id', $organisation->group_id);
        data_set($addressData, 'is_fixed', false);
        data_set($addressData, 'usage', 1);
        $addressData = Arr::only($addressData, ['group_id', 'address_line_1', 'address_line_2', 'sorting_code', 'postal_code', 'dependent_locality', 'locality', 'administrative_area', 'country_code', 'country_id', 'is_fixed', 'fixed_scope', 'usage']);

        /** @var Address $address */
        $address = Address::create($addressData);


        $organisation->updateQuietly(
            [
                'address_id' => $address->id,
            ]
        );

        return $organisation;
    }

    public function rules(): array
    {
        return [

            'address' => ['required', new ValidAddress()],

        ];
    }

    public function action(Organisation $organisation, array $modelData, int $hydratorsDelay = 0, bool $audit = true): Organisation
    {
        if (!$audit) {
            Organisation::disableAuditing();
        }
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;

        $this->initialisation($organisation, $modelData);

        return $this->handle($organisation, $this->validatedData);
    }


}
