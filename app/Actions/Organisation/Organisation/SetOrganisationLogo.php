<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 16:25:06 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Organisation\Organisation;

use App\Models\Organisation\Organisation;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SetOrganisationLogo
{
    use AsAction;

    public function handle(Organisation $organisation): void
    {

        try {
            $seed       = 'organisation-'.$organisation->id;
            $groupMedia = $organisation->addMediaFromUrl("https://api.dicebear.com/6.x/shapes/svg?seed=$seed")
                ->preservingOriginal()
                ->usingFileName($organisation->slug."-logo.sgv")
                ->toMediaCollection('logo', 'group');

            $logoId = $groupMedia->id;

            $organisation->update(['logo_id' => $logoId]);
        } catch(Exception) {
            //
        }
    }


    public string $commandSignature = 'maintenance:reset-organisation-logo {organisation : Organisation slug}';

    public function asCommand(Command $command): int
    {

        try {
            $organisation=Organisation::where('slug', $command->argument('organisation'))->firstOrFail();
        } catch (Exception) {
            $command->error('Organisation not found');
            return 1;
        }


        $this->handle($organisation);

        return 0;
    }
}
