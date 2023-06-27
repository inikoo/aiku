<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 26 Jun 2023 14:30:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailAddress\Traits;

use Illuminate\Support\Facades\Http;

trait WithCloudflareDns
{
    public function addDnsRecords(string $zoneId, array $dnsRecords): void
    {
        foreach ($dnsRecords as $dnsRecord) {
            Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('CLOUDFLARE_API_TOKEN'),
            ])->post(env('CLOUDFLARE_API_URL') . "/zones/{$zoneId}/dns_records", [
                'type' => $dnsRecord['type'],
                'name' => $dnsRecord['name'],
                'content' => $dnsRecord['content'],
                'ttl' => 1,
                'proxied' => $dnsRecord['proxied']
            ]);
        }
    }

    public function deleteDnsRecords(string $zoneId, array $dnsRecordIds): void
    {
        foreach ($dnsRecordIds as $dnsRecordId) {
            Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('CLOUDFLARE_API_TOKEN'),
            ])->delete(env('CLOUDFLARE_API_URL') . "/zones/{$zoneId}/dns_records/{$dnsRecordId}");
        }
    }

    public function registerDomain(string $domain): array
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('CLOUDFLARE_API_TOKEN'),
        ])->post(env('CLOUDFLARE_API_URL') . "/zones", [
            "name" => $domain,
            "account" => [
                "id" => env("CLOUDFLARE_ACCOUNT_ID")
            ]
        ]);

        return $response->json();
    }

    public function destroyDomain(string $zoneId): array
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('CLOUDFLARE_API_TOKEN'),
        ])->delete(env('CLOUDFLARE_API_URL') . "/zones/" . $zoneId);

        return $response->json();
    }
}
