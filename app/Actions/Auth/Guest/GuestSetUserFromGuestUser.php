<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 01 May 2023 11:03:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\Guest;

use App\Actions\Auth\User\StoreUser;
use App\Models\Auth\GroupUser;
use App\Models\Auth\Guest;
use App\Models\Auth\User;
use App\Models\Tenancy\Tenant;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class GuestSetUserFromGuestUser
{
    use AsAction;

    public function handle(Guest $guest, GroupUser $groupUser): User
    {
        return StoreUser::run(
            parent: $guest,
            groupUser: $groupUser,
        );
    }


    public string $commandSignature = 'guest:user-from-guest-user {tenant : tenant slug} {guest : guest slug} {group_user : Group user username}';

    public function asCommand(Command $command): int
    {
        try {
            $tenant = Tenant::where('slug', $command->argument('tenant'))->firstOrFail();
        } catch (Exception) {
            $command->error("Tenant {$command->argument('tenant')} not found");

            return 1;
        }
        $tenant->makeCurrent();

        try {
            $guest = Guest::where('slug', $command->argument('guest'))->firstOrFail();
        } catch (Exception) {
            $command->error("Guest {$command->argument('guest')} not found");

            return 1;
        }

        try {
            $groupUser = GroupUser::where('username', $command->argument('group_user'))->firstOrFail();
        } catch (Exception) {
            $command->error("Group-user {$command->argument('group_user')} not found");

            return 1;
        }


        $user = $this->handle($guest, $groupUser);

        $command->info("User <fg=yellow>$user->username</> for guest <fg=yellow>$guest->contact_name</> created in <fg=yellow>$tenant->slug</> 👍");

        return 0;
    }
}
