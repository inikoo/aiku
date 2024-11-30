<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\PostRoom;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Comms\PostRoom\PostRoomCodeEnum;
use App\Models\Comms\PostRoom;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdatePostRoom extends OrgAction
{
    use WithActionUpdate;

    private PostRoom $postRoom;

    public function handle(PostRoom $postRoom, array $modelData): PostRoom
    {
        return $this->update($postRoom, $modelData, ['data']);
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
            'code' => [
                'sometimes',
                'required',
                new IUnique(
                    table: 'post_rooms',
                    extraConditions: [
                        [
                            'column' => 'group_id',
                            'value'  => $this->group->id
                        ],
                        [
                            'column'   => 'id',
                            'value'    => $this->postRoom->id,
                            'operator' => '!='
                        ]
                    ]
                ),
                Rule::enum(PostRoomCodeEnum::class)
            ],
            'name' => ['sometimes', 'required', 'string', 'max:250'],
        ];
    }


    public function action(PostRoom $postRoom, $modelData): PostRoom
    {
        $this->asAction = true;
        $this->postRoom = $postRoom;

        $this->initialisationFromGroup($postRoom->group, $modelData);

        return $this->handle($postRoom, $this->validatedData);
    }


}
