<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:24:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Http\Resources\SysAdmin\UserResource;
use App\Models\SysAdmin\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class GetAllUsers
{
    use AsAction;
    use WithAttributes;

    public function handle(array $objectData = []): AnonymousResourceCollection
    {
        $query  = $objectData['query'];
        $users  = User::where('contact_name', 'ILIKE', '%'.$query.'%')->get();

        return UserResource::collection($users);
    }

    public function asController(ActionRequest $request): AnonymousResourceCollection
    {
        return $this->handle($request->all());
    }
}
