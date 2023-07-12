<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 11:54:20 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Mail\Outbox;

use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Mail\OutboxResource;
use App\Models\Mail\Outbox;
use Lorisleiva\Actions\ActionRequest;

class UpdateOutbox
{
    use WithActionUpdate;

    private bool $asAction=false;

    public function handle(Outbox $outbox, array $modelData): Outbox
    {
        return $this->update($outbox, $modelData, ['data']);
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
            'username' => ['sometimes', 'required', 'unique:tenant.outboxes', 'between:2,64', 'alpha_dash'],
            'about'    => ['sometimes', 'required', 'max:250', 'string'],
        ];
    }

    public function action(Outbox $outbox, $objectData): Outbox
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($outbox, $validatedData);
    }


    public function asController(Outbox $outbox, ActionRequest $request): Outbox
    {
        $request->validate();
        return $this->handle($outbox, $request->all());
    }


    public function jsonResponse(Outbox $outbox): OutboxResource
    {
        return new OutboxResource($outbox);
    }
}
