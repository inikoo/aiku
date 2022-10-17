<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 16 Oct 2022 10:54:53 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\CentralDomain;

use App\Models\Central\CentralDomain;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class EnableIris
{
    use AsAction;

    public string $commandSignature = 'create:iris{--d|domain= : central domain code}';

    public function handle(CentralDomain $centralDomain): void
    {
        $response = Http::acceptJson()
            ->withToken(config('iris.token'))
            ->post(config('iris.url').'/api/domains',
                   [
                       'central_domain_id' => $centralDomain->id
                   ]
            );




        if($response->status()==201){
            $centralDomain->update(['state'=>'iris-enabled']);
        }

    }

    public function asCommand(Command $command): int
    {
        if ($command->option('domain')) {
            $centralDomain = CentralDomain::where('slug', ($command->option('domain')))->firstOrFail();
            if ($centralDomain->state == 'created') {
                $this->handle($centralDomain);

                return 0;
            }
            $command->error('Central domain has state:'.$centralDomain->state);

            return 1;
        } else {
            foreach (CentralDomain::where('state', 'created') as $centralDomain) {
                $this->handle($centralDomain);
            }
        }

        return 0;
    }
}
