<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 06 Dec 2023 12:29:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop;

use App\Enums\SysAdmin\Authorisation\ShopPermissionsEnum;
use App\Enums\SysAdmin\Authorisation\RolesEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Permission;
use App\Models\SysAdmin\Role;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Permission\PermissionRegistrar;

class SeedShopPermissions
{
    use AsAction;

    public function handle(Shop $shop): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();


        $shopPermissions = collect(ShopPermissionsEnum::getAllValues($shop));


        $currentPermissions = Permission::where('scope_type', 'Shop')->where('scope_id', $shop->id)->pluck('name');
        $currentPermissions->diff($shopPermissions)
            ->each(function ($permissionName) use ($shop) {
                Permission::where('name', $permissionName)->first()->delete();
            });

        $shopPermissions->each(function ($permissionName) use ($shop) {
            try {
                Permission::create(
                    [
                        'name'       => $permissionName,
                        'scope_type' => 'Shop',
                        'scope_id'   => $shop->id,
                    ]
                );
            } catch (Exception) {
            }
        });

        $shopRoles = collect(RolesEnum::getRolesWithScope($shop));


        $currentRoles = Role::where('scope_type', 'Shop')->where('scope_id', $shop->id)->pluck('name');
        $currentRoles->diff($shopRoles)
            ->each(function ($roleName) use ($shop) {
                Role::where(
                    'name',
                    $roleName
                )->first()->delete();
            });


        foreach (RolesEnum::cases() as $case) {
            if ($case->scope() === 'Shop') {

                if (!$role = (new Role())->where('name', RolesEnum::getRoleName($case->value, $shop))->first()) {
                    $role = Role::create(
                        [
                            'name'       => RolesEnum::getRoleName($case->value, $shop),
                            'scope_type' => 'Shop',
                            'scope_id'   => $shop->id,
                            'group_id'   => $shop->group_id,
                        ]
                    );
                }
                $shopPermissions = [];

                foreach ($case->getPermissions() as $permissionName) {
                    if ($permission = (new Permission())->where('name', ShopPermissionsEnum::getPermissionName($permissionName->value, $shop))->first()) {
                        $shopPermissions[] = $permission;
                    }
                }
                $role->syncPermissions($shopPermissions);
            }
        }
    }


    public string $commandSignature = 'shop:seed-permissions';

    public function asCommand(Command $command): int
    {
        foreach (Shop::all() as $shop) {
            $command->info("Seeding permissions for shop: $shop->name");
            setPermissionsTeamId($shop->group_id);
            $this->handle($shop);
        }

        return 0;
    }

}
