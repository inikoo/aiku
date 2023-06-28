<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 16 Oct 2022 10:54:53 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Central;

use App\Models\Central\Domain;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class SetIrisDomain
{
    use AsAction;

    public string $commandSignature = 'iris:set-domain {--d|domain= : domain slug}';

    public function handle(Domain $domain): PromiseInterface|Response
    {
        $token = $domain->sysUser->createToken(
            'iris',
            ['iris'],
        );

        $parameters = [
            'central_domain_id' => $domain->id,
            'aiku_token'        => $token->plainTextToken,
            'soft'              => true,
        ];

        $response = Http::acceptJson()
            ->withToken(config('iris.token'))
            ->post(
                config('iris.url').'/api/domains',
                $parameters
            );

        if ($response->status() == 201 or $response->status() == 200) {
            $domain->update(['state' => 'iris-enabled']);
            $domain->sysUser->tokens()->where('id', '!=', $token->accessToken->id)->delete();
        }

        return $response;
    }

    public function asCommand(Command $command): int
    {
        if ($command->option('domain')) {
            /** @var Domain $domain */
            $domain = Domain::where('slug', ($command->option('domain')))->firstOrFail();
            if ($domain->state == 'created' or $domain->state == 'iris-enabled') {
                $response = $this->handle($domain);
                if (!($response->status() == 201 or $response->status() == 200)) {
                    $command->error($response->status());

                    return 1;
                }

                return 0;
            }
            $command->error('The domain has state:'.$domain->state);

            return 1;
        } else {
            foreach (Domain::whereIn('state', ['created', 'iris-enabled'])->get() as $domain) {
                $this->handle($domain);
            }
        }

        return 0;
    }
}
