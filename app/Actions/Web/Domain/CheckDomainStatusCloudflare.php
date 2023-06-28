<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 27 Jun 2023 13:36:43 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Domain;

use App\Models\Central\Domain;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class CheckDomainStatusCloudflare
{
    use AsAction;

    public string $commandSignature = 'domain:check';

    public function handle(): void
    {
        $domains = Domain::where('cloudflare_status', \App\Enums\Cenntral\Domain\DomainCloudflareStatusEnum::PENDING->value)->get();

        foreach ($domains as $domain) {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . env('CLOUDFLARE_API_TOKEN'),
            ])->get(env('CLOUDFLARE_API_URL') . "/zones/{$domain->cloudflare_id}")->json();

            Domain::where('cloudflare_id', $response['result']['id'])->update([
                'cloudflare_status' => $response['result']['status']
            ]);
        }
    }

    public function asCommand(): void
    {
        $this->handle();
    }
}
