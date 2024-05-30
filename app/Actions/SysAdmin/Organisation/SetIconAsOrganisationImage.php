<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 09 Dec 2023 03:25:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation;

use App\Actions\Media\Media\StoreMediaFromIcon;
use App\Actions\Traits\WithAttachMediaToModel;
use App\Models\SysAdmin\Organisation;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SetIconAsOrganisationImage
{
    use AsAction;
    use WithAttachMediaToModel;

    public function handle(Organisation $organisation): Organisation
    {
        $media = StoreMediaFromIcon::run($organisation);
        $this->attachMediaToModel($organisation, $media, 'logo');
        return $organisation;
    }


    public string $commandSignature = 'org:logo {organisation : Organisation slug}';

    public function asCommand(Command $command): int
    {
        try {
            $organisation = Organisation::where('slug', $command->argument('organisation'))->firstOrFail();
        } catch (Exception) {
            $command->error('Organisation not found');

            return 1;
        }

        $result = $this->handle($organisation);
        if ($result['result'] === 'success') {
            $command->info('Logo set');

            return 0;
        } else {
            $command->error($result['message']);

            return 1;
        }


    }
}
