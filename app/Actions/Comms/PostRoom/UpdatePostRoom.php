<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\PostRoom;

use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Mail\PostRoomResource;
use App\Models\Comms\PostRoom;
use Lorisleiva\Actions\ActionRequest;

class UpdatePostRoom
{
    use WithActionUpdate;

    private bool $asAction = false;

    public function handle(PostRoom $postRoom, array $modelData): PostRoom
    {
        return $this->update($postRoom, $modelData, ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("mail.edit");
    }
    public function rules(): array
    {
        return [
            'code'         => ['sometimes', 'required', 'unique:post_rooms', 'between:2,9', 'alpha_dash'],
            'name'         => ['sometimes', 'required', 'max:250', 'string'],
        ];
    }

    public function asController(PostRoom $postRoom, ActionRequest $request): PostRoom
    {
        $request->validate();
        return $this->handle($postRoom, $request->all());
    }

    public function action(PostRoom $postRoom, $modelData): PostRoom
    {
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($postRoom, $validatedData);
    }

    public function jsonResponse(PostRoom $postRoom): PostRoomResource
    {
        return new PostRoomResource($postRoom);
    }
}
