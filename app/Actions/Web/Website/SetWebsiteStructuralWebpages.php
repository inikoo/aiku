<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 11:34:19 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Actions\Web\Webpage\StoreWebpage;
use App\Models\Organisation\Organisation;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsCommand;
use Lorisleiva\Actions\Concerns\WithAttributes;

class SetWebsiteStructuralWebpages
{
    use asCommand;
    use WithAttributes;

    public string $commandSignature = 'set:website-structural-webpages
    {tenant : tenant code}
    {website : website code}
    ';

    public function getCommandDescription(): string
    {
        return 'Create initial website webpages.';
    }

    public function handle(Website $website): void
    {
        $structure = [];

        foreach (config('blueprint.webpages.ecommerce') as $node) {
            $webpage = $this->getWebpage($website, $node['webpage'], $node['webpage-variant']);

            $structure[$node['webpage']['type']] = $webpage->id;
        }


        $website->update(
            [
                'structure' => $structure
            ]
        );
    }

    private function getWebpage($website, $webpageData, $webpageVariantData): Webpage
    {
        if ($webpage = Webpage::firstWhere('type', $webpageData['type'])) {
            $webpage->mainVariant->update(
                $webpageVariantData
            );
            return $webpage;
        }

        return StoreWebpage::run($website, $webpageData, $webpageVariantData);
    }


    public function prepareForValidation(): void
    {
        if (!$this->has('username')) {
            $this->fill(['username' => $this->get('code')]);
        }
    }


    public function asCommand(Command $command): int
    {
        $organisation = Organisation::where('slug', ($command->argument('tenant')))->firstOrFail();

        $organisation->execute(function () use ($command) {
            $website = Website::where('code', ($command->argument('website')))->firstOrFail();

            $this->handle($website);
        });


        return 0;
    }
}
