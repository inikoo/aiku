<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 11:54:20 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Mailroom\Outbox;

use App\Actions\WithActionUpdate;
use App\Http\Resources\Mail\OutboxResource;
use App\Models\Mail\Outbox;
use Lorisleiva\Actions\ActionRequest;

class UpdateOutbox
{
    use WithActionUpdate;

    public function handle(Outbox $outbox, array $modelData): Outbox
    {
        return $this->update($outbox, $modelData, ['data']);
    }
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("mail.edit");
    }
    public function rules(): array
    {
        return [
            'username' => ['sometimes', 'required'],
            'about'    => ['sometimes', 'required'],
        ];
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
