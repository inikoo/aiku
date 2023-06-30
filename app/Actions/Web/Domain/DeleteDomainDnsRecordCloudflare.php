<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 27 Jun 2023 13:36:43 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Domain;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteDomainDnsRecordCloudflare
{
    use AsAction;

    public function handle(string $zoneId, string $dnsRecordId): Response|PromiseInterface
    {
        return Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . env('CLOUDFLARE_API_TOKEN'),
        ])->delete(env('CLOUDFLARE_API_URL') . "/zones/{$zoneId}/dns_records/{$dnsRecordId}");
    }
}
