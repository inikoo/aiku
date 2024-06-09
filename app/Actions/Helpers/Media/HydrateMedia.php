<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:34:03 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Media;

use App\Actions\Helpers\Media\Hydrators\MediaHydrateMultiplicity;
use App\Actions\Helpers\Media\Hydrators\MediaHydrateUsage;
use App\Actions\HydrateModel;
use App\Models\Helpers\Media;
use Illuminate\Console\Command;

class HydrateMedia extends HydrateModel
{
    public string $commandSignature = 'media:hydrate {--id=}';

    public function handle(Media $media): void
    {
        MediaHydrateUsage::run($media);
        MediaHydrateMultiplicity::run($media);
    }

    public function asCommand(Command $command): int
    {
        $exitCode = 0;
        if ($command->option('id')) {
            $media = Media::find($command->option('id'));
            $this->handle($media);
        } else {
            $command->withProgressBar(Media::all(), function ($media) {
                if ($media) {
                    $this->handle($media);
                }
            });
            $command->info("");
        }

        return $exitCode;
    }
}
