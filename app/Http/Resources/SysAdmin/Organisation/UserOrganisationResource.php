<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 02 Jan 2024 13:21:06 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\SysAdmin\Organisation;

use App\Http\Resources\HasSelfCall;
use App\Http\Resources\UI\ShopsNavigationResource;
use App\Http\Resources\UI\WarehousesNavigationResource;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserOrganisationResource extends JsonResource
{
    use HasSelfCall;


    private static User $user;

    public function toArray($request): array
    {
        /** @var Organisation $organisation */
        $organisation = $this;

        $user = self::$user;

        return [
            'slug'                  => $organisation->slug,
            'code'                  => $organisation->code,
            'name'                  => $organisation->name,
            'logo'                  => $organisation->logoImageSources(48, 48),
            'authorised_shops'      => ShopsNavigationResource::collection($user->authorisedShops()->where('organisation_id', $organisation->id)->get()),
            'authorised_warehouses' => WarehousesNavigationResource::collection($user->authorisedWarehouses()->where('organisation_id', $organisation->id)->get()),
        ];
    }

    public static function collectionForUser($resource, User $user): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        //you can add as many params as you want.
        self::$user = $user;

        return parent::collection($resource);
    }

}
