<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Sept 2022 15:04:34 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Models\HumanResources\Employee;
use App\Models\Organisations\Organisation;
use App\Models\SysAdmin\Guest;
use App\Actions\StoreModelAction;
use App\Models\SysAdmin\User;
use App\Models\Utils\ActionResult;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;


class StoreUser extends StoreModelAction
{
    use AsAction;
    use WithAttributes;


    public function handle(array $modelData): ActionResult
    {
        $modelData['password'] = Hash::make($modelData['password']);


        $user=User::create($modelData);




        return $this->finalise($user);
    }

    public function rules(): array
    {
        return [
            'username' => 'required|alpha_dash|unique:App\Models\SysAdmin\User,username',
            'password' => ['required', app()->isLocal() ? null : Password::min(8)->uncompromised()],
            'name'     => 'sometimes|required',
            'email'    => 'sometimes|required|email'
        ];
    }


}
