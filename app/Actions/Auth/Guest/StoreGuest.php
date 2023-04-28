<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\Guest;

use App\Enums\Marketing\Shop\ShopTypeEnum;
use App\Models\Auth\Guest;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreGuest
{
    use AsAction;
    use WithAttributes;

    private bool $asAction=false;

    public function handle(array $modelData): Guest
    {
        return Guest::create($modelData);
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("shops.customers.edit");
    }

    public function rules(): array
    {
        return [
            'name'                      => ['required', 'string', 'max:255'],
            'email'                     => ['nullable', 'email'],
            'phone'                     => ['nullable', 'string'],
            'identity_document_number'  => ['nullable', 'string'],
            'identity_document_type'    => ['nullable', 'string'],
            'type'                      => ['required', Rule::in(ShopTypeEnum::values())],

        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'validation.phone' => 'xxx',
        ];
    }

    public function action(array $objectData): Guest
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($validatedData);
    }
}
