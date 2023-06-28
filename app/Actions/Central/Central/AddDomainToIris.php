<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 16 Oct 2022 10:54:53 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Central;

use App\Enums\Central\Domain\DomainIrisStatusEnum;
use App\Models\Central\Domain;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class AddDomainToIris
{
    use AsAction;

    public string $commandSignature = 'domain:add-iris {--d|domain= : domain slug} {--f|force=}';

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

        $irisStatus=[DomainIrisStatusEnum::PENDING->value];

        if ($command->option('force')) {

            $irisStatus[]=DomainIrisStatusEnum::ACTIVE->value;
        }

        if ($command->option('domain')) {
            /** @var Domain $domain */
            $domain = Domain::where('slug', ($command->option('domain')))->firstOrFail();
            if (in_array($domain->irs_status,$irisStatus)) {
                $response = $this->handle($domain);
                if (!($response->status() == 201 or $response->status() == 200)) {
                    $command->error($response->status());
                    return 1;
                }

                return 0;
            }
            $command->error('Skip, domain has status:'.$domain->irs_status);

            return 1;
        } else {
            foreach (Domain::whereIn('iris_status',$irisStatus)->get() as $domain) {
                $this->handle($domain);
            }
        }

        return 0;
    }
}
