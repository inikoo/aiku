<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 30 Apr 2023 20:26:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\GroupUser;

use App\Models\Auth\GroupUser;
use App\Models\Tenancy\Group;
use App\Rules\AlphaDashDot;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreGroupUser
{
    use AsAction;
    use WithAttributes;

    private bool $trusted = false;


    public function handle(array $modelData): GroupUser
    {
        $modelData['password']  = Hash::make($modelData['password']);
        $centralUser            = GroupUser::create($modelData);

        return SetGroupUserAvatar::run($centralUser);
    }
    public function authorize(ActionRequest $request): bool
    {
        if ($this->trusted) {
            return true;
        }

        return $request->user()->hasPermissionTo("sysadmin.edit");
    }
    public function rules(): array
    {
        return [
            'username' => ['required', new AlphaDashDot(), 'unique:App\Models\Auth\GroupUser,username', Rule::notIn(['export', 'create'])],
            'password' => ['required', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()],
            'email'    => ['required', 'email', 'unique:App\Models\SysAdmin\SysUser,email']
        ];
    }

    public function action(array $objectData): GroupUser
    {
        $this->trusted = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($validatedData);
    }

    public string $commandSignature = 'create:group-user {group_slug} {username} {name} {email} {--a|autoPassword}';

    public function asCommand(Command $command): int
    {
        $this->trusted = true;


        try {
            $group = Group::where('slug', $command->argument('group_slug'))->firstOrFail();
        } catch (Exception) {
            $command->error("Group {$command->argument('group_slug')} not found");
            return 1;
        }


        if($group->tenants()->count()==0) {
            $command->error("Group {$command->argument('group_slug')} dont have any tenant,  please add one");
            return 1;
        }

        $group->owner->makeCurrent();



        if ($command->option('autoPassword')) {
            $password = (app()->isProduction() ? wordwrap(Str::random(), 4, '-', true) : 'hello');
        } else {
            $password = $command->secret('What is the password?');

        }


        $this->fill([
            'username' => $command->argument('username'),
            'password' => $password,
            'name'     => $command->argument('name'),
            'email'    => $command->argument('email'),
        ]);


        $validatedData = $this->validateAttributes();


        $groupUser=$this->handle($validatedData);


        $command->info("Group User <fg=yellow>$groupUser->username</> created 👍");

        return 0;
    }


}
