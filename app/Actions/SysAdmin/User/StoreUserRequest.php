<?php
/*
 * author Arya Permana - Kirin
 * created on 20-11-2024-16h-30m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\SysAdmin\User;

use AlibabaCloud\SDK\Dm\V20151123\Models\GetIpfilterListResponseBody\data;
use App\Actions\GrpAction;
use App\Actions\Helpers\UniversalSearch\Trait\WithSectionsRoute;
use App\Actions\Inventory\Location\Search\LocationRecordSearch;
use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydrateLocations;
use App\Actions\Inventory\WarehouseArea\Hydrators\WarehouseAreaHydrateLocations;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateLocations;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateLocations;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\User;
use App\Models\SysAdmin\UserRequest;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Stevebauman\Location\Facades\Location as FacadesLocation;

class StoreUserRequest extends GrpAction
{
    use WithNoStrictRules;

    /**
     * @throws \Throwable
     */
    public function handle(User $user, array $modelData): UserRequest
    {
        data_set($modelData, 'group_id', $user->group_id);
        $userRequest = $user->userRequests()->create($modelData);

        return $userRequest;
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
        $rules = [
            'date' => ['required', 'date'],
            'os'   => ['required', 'string'],
            'route_name' => ['required', 'string'],
            'route_params' => ['required'],
            'section'   => ['required', 'string'],
            'device' => ['required', 'string'],
            'browser' => ['required', 'string'],
            'ip_address' => ['required', 'string'],
            'location'  => ['required']
        ];

        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }
    /**
     * @throws \Throwable
     */
    public function action(User $user, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true): UserRequest
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;

        $this->initialisation($user->group, $modelData);

        return $this->handle($user, $this->validatedData);
    }
}
