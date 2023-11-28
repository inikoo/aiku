<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailroom;

use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Mail\MailroomResource;
use App\Models\Mail\Mailroom;
use Lorisleiva\Actions\ActionRequest;

class UpdateMailroom
{
    use WithActionUpdate;

    private bool $asAction=false;

    public function handle(Mailroom $mailroom, array $modelData): Mailroom
    {
        return $this->update($mailroom, $modelData, ['data']);
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
            'code'         => ['sometimes', 'required', 'unique:mailrooms', 'between:2,9', 'alpha_dash'],
            'name'         => ['sometimes', 'required', 'max:250', 'string'],
        ];
    }

    public function asController(Mailroom $mailroom, ActionRequest $request): Mailroom
    {
        $request->validate();
        return $this->handle($mailroom, $request->all());
    }

    public function action(Mailroom $mailroom, $objectData): Mailroom
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($mailroom, $validatedData);
    }

    public function jsonResponse(Mailroom $mailroom): MailroomResource
    {
        return new MailroomResource($mailroom);
    }
}
