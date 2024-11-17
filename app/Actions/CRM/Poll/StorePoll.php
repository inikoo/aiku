<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 17 Nov 2024 15:01:28 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Poll;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Enums\CRM\Poll\PollTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Poll;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StorePoll extends OrgAction
{
    use HasWebAuthorisation;

    public function handle(Shop $shop, array $modelData): Poll
    {
        data_set($modelData, 'group_id', $shop->group_id);
        data_set($modelData, 'organisation_id', $shop->organisation_id);

        $poll = $shop->polls()->create($modelData);

        return $poll;
    }

    public function rules(): array
    {
        $rules = [
            'name'                      => ['required', 'string'],
            'label'                     => ['required', 'string'],
            'in_registration'           => ['required', 'boolean'],
            'in_registration_required'  => ['required', 'boolean'],
            'type'                      => ['required', Rule::enum(PollTypeEnum::class)],
        ];

        return $rules;
    }

    public function asController(Shop $shop, ActionRequest $request): Poll
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData);
    }

    public function action(Shop $shop, array $modelData): Poll
    {
        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($shop, $this->validatedData);
    }

}
