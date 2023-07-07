<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 07 Jul 2023 08:39:42 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User;

use App\Http\Resources\SysAdmin\UserResource;
use App\Models\Auth\User;
use App\Models\Tenancy\Tenant;
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
        $users = User::where('username', 'ILIKE', '%'.$query.'%')->get();

        return UserResource::collection($users);
    }

    public function asController(ActionRequest $request): AnonymousResourceCollection
    {
        return $this->handle($request->all());
    }
}
