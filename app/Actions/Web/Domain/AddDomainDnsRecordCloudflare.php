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
use Lorisleiva\Actions\Concerns\WithAttributes;

class AddDomainDnsRecordCloudflare
{
    use AsAction;
    use WithAttributes;

    public function handle(string $zoneId, array $dnsRecord): PromiseInterface|Response
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('CLOUDFLARE_API_TOKEN'),
        ])->post(env('CLOUDFLARE_API_URL') . "/zones/{$zoneId}/dns_records", [
            'type' => $dnsRecord['type'],
            'name' => $dnsRecord['name'],
            'content' => $dnsRecord['content'],
            'ttl' => $dnsRecord['ttl'] ?? 1,
            'proxied' => $dnsRecord['proxied']
        ]);
    }
}
