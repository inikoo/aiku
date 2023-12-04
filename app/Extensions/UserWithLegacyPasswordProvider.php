<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jul 2023 10:15:46 Malaysia Time, Sanur, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Extensions;

use App\Actions\SysAdmin\User\UpdateUser;
use App\Enums\SysAdmin\User\UserAuthTypeEnum;
use App\Models\SysAdmin\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class UserWithLegacyPasswordProvider extends EloquentUserProvider
{
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {

        if (is_null($plain = $credentials['password'])) {
            return false;
        }


        if($user instanceof User && $user->auth_type==UserAuthTypeEnum::AURORA) {


            if(hash('sha256', $plain)==$user->legacy_password) {

                UpdateUser::run(
                    $user,
                    [
                        'password'=> $plain
                    ]
                );

                return true;

            }
            return false;


        } else {
            return $this->hasher->check($plain, $user->getAuthPassword());
        }



    }

}
