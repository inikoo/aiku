<?php
/*
 * author Arya Permana - Kirin
 * created on 14-11-2024-09h-29m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Web\CustomerPoll;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Web\CustomerPoll\CustomerPollTypeEnum;
use App\Models\Web\CustomerPoll;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateCustomerPoll extends OrgAction
{
    use HasWebAuthorisation;
    use WithActionUpdate;

    public function handle(CustomerPoll $customerPoll, array $modelData): CustomerPoll
    {
        $customerPoll = $this->update($customerPoll, $modelData);

        return $customerPoll;
    }

    public function rules(): array
    {
        $rules = [
            'name'                      => ['sometimes', 'string'],
            'label'                     => ['sometimes', 'string'],
            'in_registration'           => ['sometimes', 'boolean'],
            'in_registration_required'  => ['sometimes', 'boolean'],
            'type'                      => ['sometimes', Rule::enum(CustomerPollTypeEnum::class)],
        ];

        return $rules;
    }

    public function asController(CustomerPoll $customerPoll, ActionRequest $request): CustomerPoll
    {
        $this->initialisationFromShop($customerPoll->shop, $request);

        return $this->handle($customerPoll, $this->validatedData);
    }

    public function action(CustomerPoll $customerPoll, array $modelData): CustomerPoll
    {
        $this->initialisationFromShop($customerPoll->shop, $modelData);

        return $this->handle($customerPoll, $this->validatedData);
    }

}
