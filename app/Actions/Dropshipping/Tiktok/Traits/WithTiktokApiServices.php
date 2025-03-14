<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 11 Oct 2023 17:06:28 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Tiktok\Traits;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

trait WithTiktokApiServices
{
    public function generateSignature($path, $params, $appSecret, $body): string
    {
        ksort($params);

        $baseString = $appSecret . $path;

        foreach ($params as $key => $value) {
            $baseString .= $key . $value;
        }

        $jsonBody = json_encode($body);

        if (blank($jsonBody)) {
            $baseString .= $appSecret;
        } else {
            $baseString .=  $jsonBody . $appSecret;
        }

        return hash_hmac('sha256', $baseString, $appSecret);
    }

    public function restApi($path = null, $body = [], bool $requireShopCipher = true, array $headers = [], bool $requireSign = true): PendingRequest
    {
        $params = [];
        $timestamp = now()->timestamp;
        $appKey = config('services.tiktok.client_id');
        $appSecret = config('services.tiktok.client_secret');

        $shopCipher = [];
        if ($requireShopCipher) {
            $shopCipher = [
                'shop_cipher' => "GCP_PyXLKAAAAAAeMx5cq6rC6rxt1v6fM0La"
            ];
        }

        $params = array_merge($params, [
            'app_key' => $appKey,
            'timestamp' => $timestamp,
            ...$shopCipher
        ]);

        $signature = $this->generateSignature($path, $params, $appSecret, $body);

        if ($requireSign) {
            $params = array_merge($params, ['sign' => $signature]);
        }

        return Http::withHeaders([
            'x-tts-access-token' => $this->access_token,
            ...$headers
        ])->baseUrl(config('services.tiktok.base_url'))
            ->withQueryParameters($params);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function makeApiRequest(string $method, string $path, array $productData = [], bool $requireShopCipher = true, array $headers = [], bool $requireSign = true)
    {
        try {
            $apiRequest = $this->restApi($path, $productData, $requireShopCipher, $headers, $requireSign);

            $response = match (strtoupper($method)) {
                'POST' => $apiRequest->post($path, $productData),
                'GET' => $apiRequest->get($path),
                'PATCH' => $apiRequest->patch($path, $productData),
                default => throw new \Exception("Unsupported HTTP method: $method"),
            };

            if (!Arr::get($response->json(), 'data')) {
                throw new \Exception(Arr::get($response->json(), 'message'));
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('API Request failed: ' . $e->getMessage());
            throw ValidationException::withMessages(['message' => $e->getMessage()]);
        }
    }

    public function getAuthorizedShop(): array
    {
        $path = '/authorization/202309/shops';

        return $this->makeApiRequest('GET', $path, [], false, [
            'content-type' => 'application/json'
        ]);
    }

    public function uploadProductImageToTiktok(array $productData): array
    {
        $path = '/product/202309/images/upload';

        return $this->makeApiRequest('POST', $path, $productData, false, [
            'content-type' => 'multipart/form-data'
        ]);
    }

    public function uploadProductToTiktok(array $productData): array
    {
        $path = '/product/202309/products';

        return $this->makeApiRequest('POST', $path, $productData, true, [
            'content-type' => 'application/json'
        ]);
    }
}
