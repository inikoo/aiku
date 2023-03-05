<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 11:34:19 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Actions\Web\Webnode\StoreWebnode;
use App\Models\Central\Tenant;
use App\Models\Web\Webnode;
use App\Models\Web\Website;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsCommand;
use Lorisleiva\Actions\Concerns\WithAttributes;

class SetWebsiteStructuralWebnodes
{
    use asCommand;
    use WithAttributes;

    public string $commandSignature = 'set:website-structural-webnodes
    {tenant : tenant code}
    {website : website code}
    ';

    public function getCommandDescription(): string
    {
        return 'Create initial website nodes (webpages).';
    }

    public function handle(Website $website): void
    {
        $webnodes = [];

        foreach (config('blueprint.webnodes.ecommerce') as $node) {
            $webnode = $this->getWebnode($website, $node['webnode'], $node['webpage']);

            $webnodes[$node['webnode']['locus']] = $webnode->id;
        }


        $website->update(
            [
                'webnodes' => $webnodes
            ]
        );
    }

    private function getWebnode($website, $webnodeData, $webpageData): Webnode
    {
        if ($webnode = Webnode::firstWhere('locus', $webnodeData['locus'])) {
            $webnode->mainWebpage->update(
                $webpageData
            );
            return $webnode;
        }

        return StoreWebnode::run($website, $webnodeData, $webpageData);
    }


    public function prepareForValidation(): void
    {
        if (!$this->has('username')) {
            $this->fill(['username' => $this->get('code')]);
        }
    }


    public function asCommand(Command $command): int
    {
        $tenant = Tenant::where('code', ($command->argument('tenant')))->firstOrFail();

        $tenant->execute(function () use ($command) {
            $website = Website::where('code', ($command->argument('website')))->firstOrFail();

            $this->handle($website);
        });


        return 0;
    }
}
