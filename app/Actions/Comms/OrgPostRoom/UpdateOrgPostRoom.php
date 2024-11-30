<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 30 Nov 2024 00:29:23 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\OrgPostRoom;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Comms\OrgPostRoom;
use Lorisleiva\Actions\ActionRequest;

class UpdateOrgPostRoom extends OrgAction
{
    use WithActionUpdate;

    private OrgPostRoom $orgPostRoom;

    public function handle(OrgPostRoom $orgPostRoom, array $modelData): OrgPostRoom
    {
        return $this->update($orgPostRoom, $modelData, ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:250'],
        ];
    }


    public function action(OrgPostRoom $orgPostRoom, $modelData): OrgPostRoom
    {
        $this->asAction    = true;
        $this->orgPostRoom = $orgPostRoom;

        $this->initialisation($orgPostRoom->organisation, $modelData);

        return $this->handle($orgPostRoom, $this->validatedData);
    }


}
