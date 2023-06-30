<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 16 Oct 2022 10:54:53 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Central;

use App\Enums\Central\Domain\DomainIrisStatusEnum;
use App\Models\Central\Domain;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class AddDomainToIris
{
    use AsAction;

    public string $commandSignature = 'domain:add-iris {--d|domain= : domain slug} {--a|include_active}  {--r|reset_iris}';

    public function handle(Domain $domain, $resetIris = false): string
    {
        $url = match (app()->environment()) {
            'production' => $domain->domain,
            'staging'    => $domain->slug.'.'.config('app.staging_domain'),
            default      => $domain->slug.'.test'
        };


        $result = 'none';

        $getResponse = Http::acceptJson()
            ->withToken(config('iris.token'))
            ->get(
                config('iris.url')."/api/domains/$domain->id/instance"
            );


        $deleted = false;

        if ($getResponse->ok()) {
            $result = 'found';
            if ($resetIris) {
                Http::acceptJson()
                    ->withToken(config('iris.token'))
                    ->delete(
                        config('iris.url')."/api/instances/".Arr::get($getResponse->json('data'), 'id')
                    );

                $deleted = true;
                $result  = 'found_in_domain_and_deleted';
            }
        }

        if ($resetIris) {
            Http::acceptJson()
                ->withToken(config('iris.token'))
                ->delete(
                    config('iris.url')."/api/instances/url/".$url
                );

            $deleted = true;
            $result  = 'found_by_url_and_deleted';
        }


        if ($getResponse->notFound() or $deleted) {
            $token = $domain->sysUser->createToken(
                'iris',
                [
                    'iris',
                    'domain-'.$domain->slug,
                    'domain-'.$domain->id,
                    'website-'.$domain->website_id,
                    'shop-'.$domain->shop_id
                ],
            );

            $parameters = [
                'aiku_token' => $token->plainTextToken,
                'url'        => $url
            ];

            $response = Http::acceptJson()
                ->withToken(config('iris.token'))
                ->post(
                    config('iris.url')."/api/domains/$domain->id/instance",
                    $parameters
                );


            if ($response->status() == 201 or $response->status() == 200) {
                if (str_contains($result, 'found')) {
                    $result .= '_and_added';
                } else {
                    $result = 'added';
                }


                $domain->update(
                    [
                        'iris_id'     => Arr::get($response->json('data'), 'id'),
                        'iris_status' => DomainIrisStatusEnum::ACTIVE
                    ]
                );
                $domain->sysUser->tokens()->where('id', '!=', $token->accessToken->id)->delete();
            } else {
                $result = 'error_'.$response->status().' '.$response->json('message');
            }
        }

        return $result;
    }

    public function asCommand(Command $command): int
    {
        $irisStatus = [DomainIrisStatusEnum::PENDING->value];

        if ($command->option('include_active')) {
            $irisStatus[] = DomainIrisStatusEnum::ACTIVE->value;
        }

        if ($command->option('domain')) {
            /** @var Domain $domain */
            $domain = Domain::where('slug', ($command->option('domain')))->firstOrFail();
            if (in_array($domain->iris_status, $irisStatus)) {
                $result = $this->handle($domain, $command->option('reset_iris'));
                if (preg_match('/error|none/', $result)) {
                    $command->error($result);

                    return 1;
                } else {
                    $command->line($result);
                }

                return 0;
            }
            $command->error('Skip, domain has status:'.$domain->iris_status);

            return 1;
        } else {
            foreach (Domain::whereIn('iris_status', $irisStatus)->get() as $domain) {
                $this->handle($domain, $command->option('reset_iris'));
            }
        }

        return 0;
    }
}
