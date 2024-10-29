<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Mail\Mailshot\MailshotStateEnum;
use App\Http\Resources\Mail\MailshotResource;
use App\Models\Mail\Mailshot;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateMailshot extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;


    public function handle(Mailshot $mailshot, array $modelData): Mailshot
    {
        return $this->update($mailshot, $modelData, ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        //todo
        return false;
    }

    public function rules(): array
    {
        $rules = [
            'subject'           => ['sometimes','required', 'string', 'max:255'],
            'state'             => ['required', Rule::enum(MailshotStateEnum::class)],
            'recipients_recipe' => ['present', 'array']
        ];

        if (!$this->strict) {
            $rules = $this->noStrictUpdateRules($rules);
        }

        return $rules;
    }

    public function action(Mailshot $mailshot, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): Mailshot
    {
        $this->strict = $strict;
        if (!$audit) {
            Mailshot::disableAuditing();
        }
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($mailshot->shop, $modelData);

        return $this->handle($mailshot, $this->validatedData);
    }
    public function asController(Mailshot $mailshot, ActionRequest $request): Mailshot
    {
        $this->initialisationFromShop($mailshot->shop, $request);

        return $this->handle($mailshot, $this->validatedData);
    }


    public function jsonResponse(Mailshot $mailshot): MailshotResource
    {
        return new MailshotResource($mailshot);
    }
}
