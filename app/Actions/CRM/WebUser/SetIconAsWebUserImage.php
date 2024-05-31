<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Feb 2024 05:44:28 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\WebUser;

use App\Actions\Studio\Media\StoreMediaFromIcon;
use App\Actions\Traits\WithAttachMediaToModel;
use App\Models\CRM\WebUser;
use Exception;
use Illuminate\Console\Command;

class SetIconAsWebUserImage
{
    use WithAttachMediaToModel;
    public function handle(WebUser $webUser, bool $saveHistory = true): WebUser
    {
        $media = StoreMediaFromIcon::run($webUser);
        $this->attachMediaToModel($webUser, $media, 'avatar');
        return $webUser;
    }

    public string $commandSignature = 'web-user:avatar {slug : Web user slug}';

    public function asCommand(Command $command): int
    {
        try {
            $webUser = WebUser::where('slug', $command->argument('slug'))->firstOrFail();
        } catch (Exception) {
            $command->error('User not found');
            return 1;
        }

        $result = $this->handle($webUser);

        if ($result['result'] === 'success') {
            $command->info('Avatar set');
            return 0;
        } else {
            $command->error($result['message']);

            return 1;
        }
    }
}
