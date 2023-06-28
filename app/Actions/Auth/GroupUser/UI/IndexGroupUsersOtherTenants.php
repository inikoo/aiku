<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 27 Jun 2023 14:15:16 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\GroupUser\UI;

use App\Http\Resources\Auth\GroupUserResource;
use App\Models\Auth\GroupUser;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Spatie\QueryBuilder\QueryBuilder;

class IndexGroupUsersOtherTenants
{
    use AsController;


    public function handle()
    {

        return QueryBuilder::for(GroupUser::class)
            ->select('group_users.id', 'username', 'status', 'contact_name', 'email')
            ->leftJoin('public.group_user_tenant', 'public.group_user_tenant.group_user_id', 'group_users.id')
            ->where('tenant_id', '!=', app('currentTenant')->id)
            ->groupBy('group_users.id')
            ->defaultSort('username')
            ->allowedFilters(['username', 'name'])
            ->jsonPaginate();
    }

    public function asController(ActionRequest $request): AnonymousResourceCollection
    {

        $groupUsers=$this->handle();

        return GroupUserResource::collection($groupUsers);


    }
}
