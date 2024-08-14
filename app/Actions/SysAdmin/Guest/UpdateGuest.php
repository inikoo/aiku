<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:23:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest;

use App\Actions\GrpAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\SysAdmin\GuestResource;
use App\Models\SysAdmin\Guest;
use App\Rules\IUnique;
use App\Rules\Phone;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateGuest extends GrpAction
{
    use WithActionUpdate;


    private bool $validatePhone = false;

    private Guest $guest;

    public function handle(Guest $guest, array $modelData): Guest
    {
        $guest = $this->update($guest, $modelData, [
            'data',
        ]);

        if ($guest->wasChanged('status')) {
            if (!$guest->status) {
                $guest->user->update(
                    [
                        'status' => $guest->status
                    ]
                );
            }
        }

        return $guest;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo('sysadmin.edit');
    }

    public function rules(): array
    {
        $phoneValidation = ['sometimes', 'nullable'];

        if ($this->validatePhone) {
            $phoneValidation[] = new Phone();
        }


        return [
            'code'                    => [
                'sometimes',
                'string',
                'max:12',
                Rule::notIn(['export', 'create']),
                new IUnique(
                    table: 'guests',
                    extraConditions: [
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->guest->id
                        ],
                    ]
                ),

            ],
            'company_name'             => ['sometimes', 'nullable', 'string', 'max:255'],
            'contact_name'             => ['sometimes', 'required', 'string', 'max:255'],
            'email'                    => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone'                    => $phoneValidation,
            'identity_document_number' => ['sometimes', 'nullable', 'string'],
            'identity_document_type'   => ['sometimes', 'nullable', 'string'],
        ];
    }


    public function asController(Guest $guest, ActionRequest $request): Guest
    {
        $this->guest = $guest;

        $this->initialisation(group(), $request);

        return $this->handle($guest, $this->validatedData);
    }

    public function action(Guest $guest, $modelData): Guest
    {
        $this->asAction = true;
        $this->guest    = $guest;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($guest, $validatedData);
    }

    public function jsonResponse(Guest $guest): GuestResource
    {
        return new GuestResource($guest);
    }
}
