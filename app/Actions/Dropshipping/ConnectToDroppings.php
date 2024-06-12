<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 26 May 2024 15:37:54 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping;

use App\Actions\Traits\WithActionUpdate;
use App\Models\SysAdmin\Group;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class ConnectToDroppings
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    private bool $asAction = false;


    public function handle(Group $group): array
    {
        return [
            'api-key' => $group->createToken('ds-api')->plainTextToken,
        ];
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'exists:groups,dropshipping_integration_token'],
        ];
    }

    public function asController(ActionRequest $request): array
    {
        $this->fillFromRequest($request);

        $validatedData = $this->validateAttributes();

        $group = Group::where('dropshipping_integration_token', $validatedData['token'])->first();

        return $this->handle($group);
    }
}
