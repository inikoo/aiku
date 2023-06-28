<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 12 Nov 2022 16:35:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Central;

use App\Models\Central\Domain;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class RefreshDomainTokens
{
    use AsAction;

    public string $commandSignature = 'maintenance:refresh-domain-tokens {--d|domain= : domain slug}';

    public function handle(Domain $domain): PromiseInterface|Response
    {
        $token = $domain->sysUser->createToken(
            'iris',
            ['iris'],
        );

        $response = Http::acceptJson()
            ->withToken(config('iris.token'))
            ->patch(
                config('iris.url').'/api/domains/'.$domain->slug,
                [
                    'aiku_token' => $token->plainTextToken
                ]
            );

        if ($response->status() == 200) {
            $domain->sysUser->tokens()->where('id', '!=', $token->accessToken->id)->delete();
        }

        return $response;
    }

    public function asCommand(Command $command): int
    {
        if ($command->option('domain')) {
            $domain = Domain::where('slug', ($command->option('domain')))->firstOrFail();
            if ($domain->state == 'iris-enabled') {
                $response = $this->handle($domain);
                if (!($response->status() == 201 or $response->status() == 200)) {
                    $command->error($response->status());

                    return 1;
                }
                $command->line("Token for $domain->slug updated 👌");
                return 0;
            }
            $command->error('The domain has state:'.$domain->state);

            return 1;
        } else {
            foreach (Domain::where('state', 'iris-enabled')->get() as $domain) {
                $response=$this->handle($domain);
                if (!($response->status() == 201 or $response->status() == 200)) {
                    $command->error($domain->slug.': 😭 '.$response->status());
                } else {
                    $command->line("Token for $domain->slug updated 👌");
                }
            }
        }

        return 0;
    }
}
