<?php

namespace App\Actions\CRM\Appointment\Zoom;

use App\Models\Catalogue\Shop;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsObject;

class ZoomClient
{
    use AsObject;

    private const string TOKEN_URL = 'https://zoom.us/oauth/token';

    private const string API_URL = 'https://api.zoom.us/v2';

    public function isConfigured(Shop $shop): bool
    {
        $credentials = $this->credentials($shop);

        return $credentials['account_id'] && $credentials['client_id'] && $credentials['client_secret'];
    }

    /**
     * @return array{account_id: string|null, client_id: string|null, client_secret: string|null, host: string}
     */
    public function credentials(Shop $shop): array
    {
        $zoom   = Arr::get($shop->settings, 'zoom', []);
        $secret = Arr::get($zoom, 'client_secret');

        return [
            'account_id'    => Arr::get($zoom, 'account_id') ?: null,
            'client_id'     => Arr::get($zoom, 'client_id') ?: null,
            'client_secret' => $secret ? Crypt::decryptString($secret) : null,
            'host'          => Arr::get($zoom, 'host_email') ?: 'me',
        ];
    }

    /**
     * @throws RequestException
     */
    public function requestAccessToken(string $accountId, string $clientId, string $clientSecret): string
    {
        return Http::asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->timeout(15)
            ->post(self::TOKEN_URL, [
                'grant_type' => 'account_credentials',
                'account_id' => $accountId,
            ])
            ->throw()
            ->json('access_token');
    }

    /**
     * @param array<string, mixed> $meeting
     * @return array{id: int|string, join_url: string}
     * @throws RequestException
     */
    public function createMeeting(Shop $shop, array $meeting): array
    {
        $host = rawurlencode($this->credentials($shop)['host']);

        return $this->api($shop)->post(self::API_URL."/users/$host/meetings", $meeting)->throw()->json();
    }

    /**
     * @param array<string, mixed> $meeting
     * @throws RequestException
     */
    public function updateMeeting(Shop $shop, string $meetingId, array $meeting): void
    {
        $this->api($shop)->patch(self::API_URL.'/meetings/'.rawurlencode($meetingId), $meeting)->throw();
    }

    /**
     * @throws RequestException
     */
    public function deleteMeeting(Shop $shop, string $meetingId): void
    {
        $response = $this->api($shop)->delete(self::API_URL.'/meetings/'.rawurlencode($meetingId));

        if ($response->status() === 404) {
            return;
        }

        $response->throw();
    }

    private function api(Shop $shop): PendingRequest
    {
        $credentials = $this->credentials($shop);
        $cacheKey    = 'zoom-token-'.$shop->id.'-'.md5($credentials['account_id'].$credentials['client_id'].$credentials['client_secret']);

        $token = Cache::remember(
            $cacheKey,
            now()->addMinutes(50),
            fn () => $this->requestAccessToken($credentials['account_id'], $credentials['client_id'], $credentials['client_secret'])
        );

        return Http::withToken($token)->acceptJson()->timeout(15);
    }
}
