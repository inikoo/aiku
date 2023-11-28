<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreMailshot
{
    use AsAction;
    use WithAttributes;
    private bool $asAction=false;

    public function handle(Outbox $outbox, array $modelData): Mailshot
    {
        $modelData['shop_id']=$outbox->shop_id;
        /** @var Mailshot $mailshot */
        $mailshot = $outbox->mailshots()->create($modelData);
        $mailshot->stats()->create();

        return $mailshot;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("mail.edit");
    }

    /*
    public function rules(): array
    {
        return [
            'code'         => ['required', 'unique:mailshots', 'between:2,256', 'alpha_dash'],
            'name'         => ['required', 'max:250', 'string'],
        ];
    }
    */

    public function action(Outbox $outbox, array $objectData): Mailshot
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($outbox, $validatedData);
    }
}
