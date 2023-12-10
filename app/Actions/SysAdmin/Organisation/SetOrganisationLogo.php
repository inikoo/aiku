<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 09 Dec 2023 03:25:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation;

use App\Actions\Helpers\Avatars\GetDiceBearAvatar;
use App\Enums\Helpers\Avatars\DiceBearStylesEnum;
use App\Models\Media\Media;
use App\Models\SysAdmin\Organisation;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SetOrganisationLogo
{
    use AsAction;

    public function handle(Organisation $organisation): array
    {
        try {
            $seed = 'organisation-'.$organisation->id;
            /** @var Media $media */
            $media = $organisation->addMediaFromString(GetDiceBearAvatar::run(DiceBearStylesEnum::RINGS, $seed))
                ->preservingOriginal()
                ->withProperties(
                    [
                        'group_id' => $organisation->group_id
                    ]
                )
                ->usingFileName($organisation->slug."-logo.sgv")
                ->toMediaCollection('logo');

            $logoId = $media->id;

            $organisation->update(['logo_id' => $logoId]);

            return ['result' => 'success'];
        } catch (Exception $e) {
            return ['result' => 'error', 'message' => $e->getMessage()];
        }
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
