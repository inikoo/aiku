<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 08 Jun 2023 14:34:48 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User;

use App\Actions\Auth\GroupUser\Hydrators\GroupUserHydrateTenants;
use App\Actions\Auth\GroupUser\StoreGroupUser;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateUsers;
use App\Models\Auth\GroupUser;
use App\Models\Auth\Guest;
use App\Models\Auth\User;
use App\Models\HumanResources\Employee;
use App\Rules\AlphaDashDot;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Illuminate\Validation\Validator;
use OwenIt\Auditing\Models\Audit;

class StoreUserHistories
{
    use AsAction;
    use WithAttributes;

    public function handle(array $objectData = []): Audit
    {
        return Audit::create($objectData);
    }
}
