<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 17 Nov 2024 15:01:28 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Poll;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\CRM\Poll\PollTypeEnum;
use App\Models\CRM\Poll;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdatePoll extends OrgAction
{
    use HasWebAuthorisation;
    use WithActionUpdate;

    public function handle(Poll $poll, array $modelData): Poll
    {
        $poll = $this->update($poll, $modelData);

        return $poll;
    }

    public function rules(): array
    {
        $rules = [
            'name'                      => ['sometimes', 'string'],
            'label'                     => ['sometimes', 'string'],
            'in_registration'           => ['sometimes', 'boolean'],
            'in_registration_required'  => ['sometimes', 'boolean'],
            'type'                      => ['sometimes', Rule::enum(PollTypeEnum::class)],
        ];

        return $rules;
    }

    public function asController(Poll $poll, ActionRequest $request): Poll
    {
        $this->initialisationFromShop($poll->shop, $request);

        return $this->handle($poll, $this->validatedData);
    }

    public function action(Poll $poll, array $modelData): Poll
    {
        $this->initialisationFromShop($poll->shop, $modelData);

        return $this->handle($poll, $this->validatedData);
    }

}
