<?php

/*
 * author Arya Permana - Kirin
 * created on 03-01-2025-10h-39m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\HumanResources\Employee\UI;

use App\Actions\SysAdmin\User\GetUserGroupScopeJobPositionsData;
use App\Actions\SysAdmin\User\GetUserOrganisationScopeJobPositionsData;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Http\Resources\Api\Dropshipping\ShopResource;
use App\Http\Resources\HumanResources\EmployeeResource;
use App\Http\Resources\HumanResources\JobPositionResource;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Http\Resources\SysAdmin\Organisation\OrganisationsResource;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\Concerns\AsObject;

class GetEmployeeShowcase
{
    use AsObject;

    public function handle(Employee $employee): array
    {
        $user = $employee->getUser();
        $pictogram = [];
        if ($user) {
            $jobPositionsOrganisationsData = [];
            foreach ($employee->group->organisations as $organisation) {
                $jobPositionsOrganisationData                       = GetUserOrganisationScopeJobPositionsData::run($user, $organisation);
                $jobPositionsOrganisationsData[$organisation->slug] = $jobPositionsOrganisationData;
            }

            $permissionsGroupData = GetUserGroupScopeJobPositionsData::run($user);
            $pictogram = [
                'organisation_list' => OrganisationsResource::collection($user->group->organisations),
                "current_organisation"  => $user->getOrganisation(),
                'options'           => Organisation::get()->flatMap(function (Organisation $organisation) {
                    return [
                        $organisation->slug => [
                            'positions'   => JobPositionResource::collection($organisation->jobPositions),
                            'shops'       => \App\Http\Resources\Catalogue\ShopResource::collection($organisation->shops()->where('type', '!=', ShopTypeEnum::FULFILMENT)->get()),
                            'fulfilments' => ShopResource::collection($organisation->shops()->where('type', '=', ShopTypeEnum::FULFILMENT)->get()),
                            'warehouses'  => WarehouseResource::collection($organisation->warehouses),
                        ]
                    ];
                })->toArray(),
                'group' => $permissionsGroupData,
                'organisations' => $jobPositionsOrganisationsData
            ];
        }




        $permissionsGroupData = GetUserGroupScopeJobPositionsData::run($user);
        return [
            'employee' => EmployeeResource::make($employee),
            'pin'      => $employee->pin,

            'permissions_pictogram' => $pictogram
        ];
    }
}
