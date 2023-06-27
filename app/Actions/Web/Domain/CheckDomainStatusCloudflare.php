<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 27 Jun 2023 13:36:43 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Domain;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class CheckDomainStatusCloudflare
{
    use AsAction;

    public string $commandSignature   = 'domain:check {zone}';

    public function handle(string $zoneId): PromiseInterface|Response|array
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('CLOUDFLARE_API_TOKEN'),
        ])->get(env('CLOUDFLARE_API_URL') . "/zones/{$zoneId}");

        return $response->json();
    }

    public function asCommand(Command $command): void
    {
        $this->handle($command->argument('zone'));
    }
}
