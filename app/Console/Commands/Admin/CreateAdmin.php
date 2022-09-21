<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 14 Aug 2022 20:06:00 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Console\Commands\Admin;


use App\Actions\SysAdmin\AdminUser\StoreAdminUser;
use App\Models\SysAdmin\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class CreateAdmin extends Command
{

    protected $signature = 'admin:new {--randomPassword} {name} {email} {slug?} {username?}';

    protected $description = 'Create new admin user';

    public function handle(): int
    {
        if ($this->option('randomPassword')) {
            $password = (config('app.env') == 'local' ? 'hello' : wordwrap(Str::random(), 4, '-', true));
        } else {
            $password = $this->secret('What is the password?');
            if (strlen($password) < 8) {
                $this->error("Password needs to be at least 8 characters");

                return 0;
            }
        }

        $admin = new Admin([
                               'name' => $this->argument('name'),
                               'email' => $this->argument('email'),


                           ]);
        if ($this->argument('slug')) {
            $admin->slug = $this->argument('slug');
        }
        $username = $admin->slug;
        if ($this->argument('username')) {
            $username = $this->argument('slug');
        }

        $admin->save();


        $res = StoreAdminUser::run(
            $admin,
            [
                'username' => $username,
                'password' => Hash::make($password)
            ]
        );


        $this->line("Account admin created $admin->slug");

        $this->table(
            ['Code', 'Username', 'Password'],
            [
                [
                    $admin->slug,
                    $res->model->username,
                    ($this->option('randomPassword') ? $password : '*****'),
                ],

            ]
        );


        return 0;
    }
}
