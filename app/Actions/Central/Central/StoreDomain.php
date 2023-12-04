<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 15 Oct 2022 19:09:22 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Central;

use App\Actions\SysAdmin\SysUser\StoreSysUser;
use App\Enums\Web\Website\WebsiteEngineEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Models\Central\Domain;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreDomain
{
    use AsAction;

    public function handle(Organisation $organisation, Website $website, array $modelData): Domain
    {

        data_set($modelData, 'website_id', $website->id);
        data_set($modelData, 'shop_id', $website->shop_id);

        /** @var Domain $domain */
        $domain = $organisation->domains()->create($modelData);
        $domain->stats()->create();
        StoreSysUser::run(
            $domain,
            [
                'username'=> 'iris-'.$domain->slug,
                'password'=> wordwrap(Str::random(), 4, '-', true)
            ]
        );

        if($website->state!=WebsiteStateEnum::CLOSED) {
            AddDomainToIris::run(
                domain: $domain,
                resetIris: true
            );
        }
        if($website->engine==WebsiteEngineEnum::IRIS) {
            AddDomainToCloudflare::run($domain);

        }

        return $domain;
    }
}
