<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 16 Oct 2022 10:54:53 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\CentralDomain;

use App\Models\Central\CentralDomain;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class SetIrisDomain
{
    use AsAction;

    public string $commandSignature = 'set:iris-domain {--d|domain= : central domain slug}';

    public function handle(CentralDomain $centralDomain): PromiseInterface|Response
    {
        $token = $centralDomain->adminUser->createToken(
            'iris',
            ['iris'],
        );

        $parameters = [
            'central_domain_id' => $centralDomain->id,
            'pika_token'        => $token->plainTextToken,
            'soft'              => true,
        ];

        $response = Http::acceptJson()
            ->withToken(config('iris.token'))
            ->post(
                config('iris.url').'/api/domains',
                $parameters
            );

        if ($response->status() == 201 or $response->status() == 200) {
            $centralDomain->update(['state' => 'iris-enabled']);
            $centralDomain->adminUser->tokens()->where('id', '!=', $token->accessToken->id)->delete();
        }

        return $response;
    }

    public function asCommand(Command $command): int
    {
        if ($command->option('domain')) {
            $centralDomain = CentralDomain::where('slug', ($command->option('domain')))->firstOrFail();
            if ($centralDomain->state == 'created' or $centralDomain->state == 'iris-enabled') {
                $response = $this->handle($centralDomain);
                if (!($response->status() == 201 or $response->status() == 200)) {
                    $command->error($response->status());

                    return 1;
                }

                return 0;
            }
            $command->error('Central domain has state:'.$centralDomain->state);

            return 1;
        } else {
            foreach (CentralDomain::whereIn('state', ['created','iris-enabled'])->get() as $centralDomain) {
                $this->handle($centralDomain);
            }
        }

        return 0;
    }
}
