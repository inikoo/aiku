<?php

namespace Database\Seeders;

use App\Enums\SysAdmin\Authorisation\RolesEnum;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Illuminate\Database\Seeder;

class RoleBlueprintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $orgTypeShop=[];

        $roles       = collect(RolesEnum::cases());
        $permissions = $roles->map(function ($role) {
            return [$role->label() => match ($role->scope()) {
                class_basename(Group::class) => Group::all()->map(function (Group $group) {
                    return [$group->name => [
                        'organisations' => $group->organisations->pluck('slug')
                    ]];
                }),
                class_basename(Organisation::class) => [
                    'organisations' => Organisation::all()->pluck('slug')
                ],
                class_basename(Shop::class) => Organisation::all()->map(function (Organisation $organisation) {
                    return [$organisation->name => [
                        'shops' => $organisation->shops->pluck('slug')
                    ]];
                }),
                class_basename(Fulfilment::class) => Organisation::all()->map(function (Organisation $organisation) {
                    return [$organisation->name => [
                        'fulfilments' => $organisation->fulfilments->pluck('slug')
                    ]];
                }),
                default => []
            }];
        });
    }
}
