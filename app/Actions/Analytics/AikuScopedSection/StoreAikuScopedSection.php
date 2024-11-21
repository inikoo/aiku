<?php
/*
 * author Arya Permana - Kirin
 * created on 21-11-2024-13h-25m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Analytics\AikuScopedSection;

use App\Actions\GrpAction;
use App\Models\Analytics\AikuScopedSection;
use App\Models\Analytics\AikuSection;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Inventory\Warehouse;
use App\Models\Manufacturing\Production;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreAikuScopedSection extends GrpAction
{
    use AsAction;
    use WithAttributes;

    public function handle(Group|Organisation|Shop|Fulfilment|Warehouse|Production $scope, AikuSection $aikuSection, array $modelData)
    {
        if($scope instanceof Group)
        {
            data_set($modelData, 'group_id', $scope->id);
        } elseif ($scope instanceof Organisation) {
            data_set($modelData, 'group_id', $scope->group_id);
            data_set($modelData, 'organisation_id', $scope->id);
        } else {
            data_set($modelData, 'group_id', $scope->group_id);
            data_set($modelData, 'organisation_id', $scope->organisation_id);
        }

        $aikuScopedSection = $scope->aikuScopedSections()->attach($aikuSection->id, $modelData);
        return $aikuScopedSection;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function action(Group|Organisation|Shop|Fulfilment|Warehouse|Production $scope, AikuSection $aikuSection, array $modelData)
    {
        if($scope instanceof Group)
        {
            $group = $scope;
        } else {
            $group = $scope->group;
        }
        $this->initialisation($group, $modelData);

        return $this->handle($scope, $aikuSection, $modelData);
    }


}
