<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Outbox;

use App\Models\Mail\PostRoom;
use App\Models\Mail\Outbox;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreOutbox
{
    use AsAction;
    use WithAttributes;

    private bool $asAction=false;

    public function handle(PostRoom $postRoom, array $modelData): Outbox
    {
        /** @var Outbox $outbox */
        $outbox = $postRoom->outboxes()->create($modelData);
        $outbox->stats()->create();

        return $outbox;
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
            'type'         => ['required', 'unique:outboxes', 'between:2,64', 'alpha_dash'],
            'name'         => ['required', 'max:250', 'string'],
        ];
    }

    public function action(PostRoom $postRoom, array $modelData): Outbox
    {
        $this->asAction=true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($postRoom, $validatedData);
    }
}
