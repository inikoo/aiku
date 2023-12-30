<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:23:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest;

use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\SysAdmin\GuestResource;
use App\Models\SysAdmin\Guest;
use Lorisleiva\Actions\ActionRequest;

class UpdateGuest
{
    use WithActionUpdate;

    private bool $asAction = false;

    public function handle(Guest $guest, array $modelData): Guest
    {
        $guest= $this->update($guest, $modelData, [
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

        return $request->user()->hasPermissionTo("shops.customers.edit");
    }

    public function rules(): array
    {
        return [
            'contact_name'             => ['sometimes', 'required', 'string', 'max:255'],
            'email'                    => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone'                    => ['sometimes', 'nullable', 'phone:AUTO'],
            'identity_document_number' => ['sometimes', 'nullable', 'string'],
            'identity_document_type'   => ['sometimes', 'nullable', 'string'],
        ];
    }


    public function asController(Guest $guest, ActionRequest $request): Guest
    {
        $request->validate();

        return $this->handle($guest, $request->all());
    }

    public function action(Guest $guest, $modelData): Guest
    {
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($guest, $validatedData);
    }

    public function jsonResponse(Guest $guest): GuestResource
    {
        return new GuestResource($guest);
    }
}
