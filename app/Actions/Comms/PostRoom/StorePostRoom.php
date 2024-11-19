<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\PostRoom;

use App\Actions\SysAdmin\Group\Hydrators\GroupHydratePostRooms;
use App\Enums\Comms\PostRoom\PostRoomCodeEnum;
use App\Models\Comms\PostRoom;
use App\Models\SysAdmin\Group;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StorePostRoom
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    public function handle(Group $group, array $modelData): PostRoom
    {
        /** @var PostRoom $postRoom */
        $postRoom = $group->postRooms()->create($modelData);
        $postRoom->stats()->create();
        GroupHydratePostRooms::run($group);
        return $postRoom;
    }


    public function rules(): array
    {
        return [
            'code' => [Rule::enum(PostRoomCodeEnum::class)],
            'name' => ['required', 'string'],
        ];
    }

    public function action(Group $group, array $modelData): PostRoom
    {
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($group, $validatedData);
    }
}
