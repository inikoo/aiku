<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 05-03-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Comms\Outbox;

use App\Actions\OrgAction;
// use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateLocations;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Comms\Outbox;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateSubscribeOutbox extends OrgAction
{
    use WithNoStrictRules;

    /**
     * @throws \Throwable
     */
    public function handle(Outbox $parent, array $modelData)
    {
        $user = Arr::get($modelData, 'user_id');
        $parent->update([
            'external_links' => json_encode(Arr::get($modelData, 'external_links')),
            'user_id' => $user,
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo("locations.{$this->warehouse->id}.edit");
    }

    public function rules(): array
    {
        $rules = [
            'user_id'       => [
                'sometimes',
            ],
            'external_links' => [
                'required_if:external_links,null',
            ],
        ];

        return $rules;
    }

}
