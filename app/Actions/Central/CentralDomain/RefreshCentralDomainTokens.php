<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 12 Nov 2022 16:35:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\CentralDomain;

use App\Models\Central\CentralDomain;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class RefreshCentralDomainTokens
{
    use AsAction;

    public string $commandSignature = 'maintenance:refresh-domain-tokens {--d|domain= : central domain slug}';

    public function handle(CentralDomain $centralDomain): PromiseInterface|Response
    {
        $token = $centralDomain->sysUser->createToken(
            'iris',
            ['iris'],
        );

        $response = Http::acceptJson()
            ->withToken(config('iris.token'))
            ->patch(
                config('iris.url').'/api/domains/'.$centralDomain->slug,
                [
                    'aiku_token' => $token->plainTextToken
                ]
            );

        if ($response->status() == 200) {
            $centralDomain->sysUser->tokens()->where('id', '!=', $token->accessToken->id)->delete();
        }

        return $response;
    }

    public function asCommand(Command $command): int
    {
        if ($command->option('domain')) {
            $centralDomain = CentralDomain::where('slug', ($command->option('domain')))->firstOrFail();
            if ($centralDomain->state == 'iris-enabled') {
                $response = $this->handle($centralDomain);
                if (!($response->status() == 201 or $response->status() == 200)) {
                    $command->error($response->status());

                    return 1;
                }
                $command->line("Token for $centralDomain->slug updated 👌");
                return 0;
            }
            $command->error('Central domain has state:'.$centralDomain->state);

            return 1;
        } else {
            foreach (CentralDomain::where('state', 'iris-enabled')->get() as $centralDomain) {
                $response=$this->handle($centralDomain);
                if (!($response->status() == 201 or $response->status() == 200)) {
                    $command->error($centralDomain->slug.': 😭 '.$response->status());
                } else {
                    $command->line("Token for $centralDomain->slug updated 👌");
                }
            }
        }

        return 0;
    }
}
