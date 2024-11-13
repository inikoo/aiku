<?php
/*
 * author Arya Permana - Kirin
 * created on 13-11-2024-13h-43m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Mail\Mailshot;

use App\Actions\HydrateModel;
use App\Actions\Mail\Mailshot\Hydrators\MailshotHydrateEmails;
use App\Models\Mail\Mailshot;
use Illuminate\Support\Collection;

class HydrateMailshot extends HydrateModel
{
    public string $commandSignature = 'hydrate:mailshots {organisations?*} {--slugs}';

    public function handle(Mailshot $mailshot): void
    {
        MailshotHydrateEmails::run($mailshot);
    }

    protected function getModel(string $slugs): Mailshot
    {
        return Mailshot::where('slug', $slugs)->first();
    }

    protected function getAllModels(): Collection
    {
        return Mailshot::get();
    }

}
