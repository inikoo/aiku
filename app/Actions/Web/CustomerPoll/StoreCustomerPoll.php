<?php
/*
 * author Arya Permana - Kirin
 * created on 14-11-2024-09h-19m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Web\CustomerPoll;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Enums\Web\CustomerPoll\CustomerPollTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Web\CustomerPoll;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreCustomerPoll extends OrgAction
{
    use HasWebAuthorisation;

    public function handle(Shop $shop, array $modelData): CustomerPoll
    {
        data_set($modelData, 'group_id', $shop->group_id);
        data_set($modelData, 'organisation_id', $shop->organisation_id);

        $customerPoll = $shop->customerPolls()->create($modelData);

        return $customerPoll;
    }

    public function rules(): array
    {
        $rules = [
            'name'                      => ['required', 'string'],
            'label'                     => ['required', 'string'],
            'in_registration'           => ['required', 'boolean'],
            'in_registration_required'  => ['required', 'boolean'],
            'type'                      => ['required', Rule::enum(CustomerPollTypeEnum::class)],
        ];

        return $rules;
    }

    public function asController(Shop $shop, ActionRequest $request): CustomerPoll
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData);
    }

    public function action(Shop $shop, array $modelData): CustomerPoll
    {
        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($shop, $this->validatedData);
    }

}
