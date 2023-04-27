<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Actions\WithActionUpdate;
use App\Http\Resources\Mail\MailshotResource;
use App\Models\Mail\Mailshot;
use Lorisleiva\Actions\ActionRequest;

class UpdateMailshot
{
    use WithActionUpdate;

    private bool $asAction=false;

    public function handle(Mailshot $mailshot, array $modelData): Mailshot
    {
        return $this->update($mailshot, $modelData, ['data']);
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
            'code'         => ['sometimes', 'required', 'unique:tenant.mailshots', 'between:2,256', 'alpha_dash'],
            'name'         => ['sometimes', 'required', 'max:250', 'string'],
        ];
    }

    public function action(Mailshot $mailshot, $objectData): Mailshot
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($mailshot, $validatedData);
    }
    public function asController(Mailshot $mailshot, ActionRequest $request): Mailshot
    {
        $request->validate();
        return $this->handle($mailshot, $request->all());
    }


    public function jsonResponse(Mailshot $mailshot): MailshotResource
    {
        return new MailshotResource($mailshot);
    }
}
