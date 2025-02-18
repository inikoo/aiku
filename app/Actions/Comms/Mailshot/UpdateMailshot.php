<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Mailshot;

use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateMailshots;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Comms\Mailshot\MailshotStateEnum;
use App\Http\Resources\Mail\MailshotResource;
use App\Models\Comms\Mailshot;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateMailshot extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;


    public function handle(Mailshot $mailshot, array $modelData): Mailshot
    {
        $mailshot = $this->update($mailshot, $modelData, ['data']);

        if ($mailshot->wasChanged('state')) {
            GroupHydrateMailshots::dispatch($mailshot->group)->delay($this->hydratorsDelay);
        }

        return $mailshot;

    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        //todo
        return $request->user()->authTo("crm.{$this->shop->id}.edit");

    }

    public function rules(): array
    {
        $rules = [
            'subject'           => ['sometimes', 'string', 'max:255'],
            'state'             => ['sometimes', Rule::enum(MailshotStateEnum::class)],
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
