<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Mar 2023 16:50:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailroom;

use App\Models\Mail\Mailroom;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreMailroom
{
    use AsAction;
    use WithAttributes;

    private bool $asAction=false;

    public function handle(array $modelData): Mailroom
    {
        $mailroom = Mailroom::create($modelData);
        $mailroom->stats()->create();

        return $mailroom;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    }

    public function rules(): array
    {
        return [
            'code'         => ['required', 'string', 'unique:tenant.mailrooms', 'between:2,9', 'alpha_dash'],
        ];
    }

    public function action(array $objectData): Mailroom
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($validatedData);
    }
}
