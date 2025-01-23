<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Jan 2025 01:18:34 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\WebUserPasswordReset;

use App\Models\CRM\WebUserPasswordReset;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class PurgeWebUserPasswordReset
{
    use AsAction;


    public function handle(): void
    {
        WebUserPasswordReset::where('created_at', '<', now()->subDays(3))->delete();
    }

    public function getCommandSignature(): string
    {
        return 'maintenance:purge_web_user_password_reset';
    }

    public function asCommand(Command $command): int
    {
        try {
            $this->handle();
        } catch (Throwable $e) {
            $command->error($e->getMessage());

            return 1;
        }

        return 0;
    }

}
