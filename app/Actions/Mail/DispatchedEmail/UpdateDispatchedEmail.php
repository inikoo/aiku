<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\DispatchedEmail;

use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Models\Mail\DispatchedEmail;
use Lorisleiva\Actions\ActionRequest;

class UpdateDispatchedEmail
{
    use WithActionUpdate;

    private bool $asAction=false;

    public function handle(DispatchedEmail $dispatchedEmail, array $modelData): DispatchedEmail
    {
        return $this->update($dispatchedEmail, $modelData, ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("mail.edit");
    }
    public function rules(): array
    {
        return [
            'code'         => ['sometimes', 'required', 'unique:dispatched_emails', 'between:2,64', 'alpha_dash'],
        ];
    }
    public function action(DispatchedEmail $dispatchedEmail, array $modelData): DispatchedEmail
    {
        $this->asAction=true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($dispatchedEmail, $validatedData);
    }

    public function jsonResponse(DispatchedEmail $dispatchedEmail): DispatchedEmailResource
    {
        return new DispatchedEmailResource($dispatchedEmail);
    }
}
