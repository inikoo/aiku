<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Services\GoogleTagManager;

use App\Models\SysAdmin\Group;
use Google\Client;
use Google\Service\Exception as GoogleServiceException;
use Google\Service\TagManager;
use Google\Service\TagManager\Container;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redis;
use RuntimeException;
use Throwable;

/**
 * The group's one Google login talking to the Tag Manager API, read only. The login is a person who
 * holds access to every Tag Manager account, so a single refresh token lists them all.
 */
class GoogleTagManagerClient
{
    public const int CALLS_PER_MINUTE = 15;

    private function __construct(private readonly TagManager $service, private readonly string $groupId)
    {
    }

    public static function forGroup(Group $group): ?self
    {
        $encryptedToken = Arr::get($group->settings, 'google_tag_manager.refresh_token');

        if (blank($encryptedToken) || blank(config('services.google.client_id'))) {
            return null;
        }

        $client      = self::baseClient();
        $accessToken = $client->fetchAccessTokenWithRefreshToken(Crypt::decryptString($encryptedToken));

        if (array_key_exists('error', $accessToken)) {
            throw new RuntimeException('Google refused the Tag Manager refresh token: '.Arr::get($accessToken, 'error_description', $accessToken['error']));
        }

        return new self(new TagManager($client), (string) $group->id);
    }

    public static function authUrl(Group $group): string
    {
        $client = self::baseClient();
        $client->setRedirectUri(route('google_tag_manager.callback'));
        $client->setAccessType('offline');
        $client->setPrompt('consent select_account');
        $client->setState(Crypt::encryptString($group->id.'|'.now()->addMinutes(15)->timestamp));

        return $client->createAuthUrl();
    }

    public static function groupFromState(string $state): ?Group
    {
        try {
            [$groupId, $expiresAt] = explode('|', Crypt::decryptString($state));
        } catch (Throwable) {
            return null;
        }

        return (int) $expiresAt >= now()->timestamp ? Group::find((int) $groupId) : null;
    }

    public static function storeRefreshToken(Group $group, string $authCode): void
    {
        $client = self::baseClient();
        $client->setRedirectUri(route('google_tag_manager.callback'));

        $accessToken  = $client->fetchAccessTokenWithAuthCode($authCode);
        $refreshToken = Arr::get($accessToken, 'refresh_token');

        if (array_key_exists('error', $accessToken) || blank($refreshToken)) {
            throw new RuntimeException('Google did not return a Tag Manager refresh token: '.json_encode($accessToken));
        }

        $settings = $group->settings;
        data_set($settings, 'google_tag_manager.refresh_token', Crypt::encryptString($refreshToken));
        data_set($settings, 'google_tag_manager.connected_at', now()->toIso8601String());
        $group->update(['settings' => $settings]);
    }

    public static function isConnected(Group $group): bool
    {
        return filled(Arr::get($group->settings, 'google_tag_manager.refresh_token'));
    }

    /**
     * @return array<int, array{account_id: string, account_name: string, container_id: string, name: string, public_id: string, usage_context: string|null, domain_names: array<int, string>, tag_manager_url: string|null}>
     */
    public function containers(): array
    {
        $rows = [];

        foreach ($this->accounts() as $account) {
            foreach ($this->containersOf($account->getPath()) as $container) {
                $rows[] = [
                    'account_id'      => $account->getAccountId(),
                    'account_name'    => $account->getName(),
                    'container_id'    => $container->getContainerId(),
                    'name'            => $container->getName(),
                    'public_id'       => $container->getPublicId(),
                    'usage_context'   => Arr::first($container->getUsageContext() ?? []),
                    'domain_names'    => $container->getDomainName() ?? [],
                    'tag_manager_url' => $container->getTagManagerUrl(),
                ];
            }
        }

        return $rows;
    }

    /**
     * @return array<int, \Google\Service\TagManager\Account>
     */
    private function accounts(): array
    {
        $accounts  = [];
        $pageToken = null;

        do {
            $page      = $this->throttled(fn () => $this->service->accounts->listAccounts(array_filter(['pageToken' => $pageToken])));
            $accounts  = array_merge($accounts, $page->getAccount() ?? []);
            $pageToken = $page->getNextPageToken();
        } while ($pageToken);

        return $accounts;
    }

    /**
     * @return array<int, Container>
     */
    private function containersOf(string $accountPath): array
    {
        $containers = [];
        $pageToken  = null;

        do {
            $page       = $this->throttled(fn () => $this->service->accounts_containers->listAccountsContainers($accountPath, array_filter(['pageToken' => $pageToken])));
            $containers = array_merge($containers, $page->getContainer() ?? []);
            $pageToken  = $page->getNextPageToken();
        } while ($pageToken);

        return $containers;
    }

    private function throttled(callable $call): mixed
    {
        return retry(
            [2000, 10000, 30000],
            fn () => Redis::throttle('google-tag-manager:'.$this->groupId)
                ->allow(self::CALLS_PER_MINUTE)->every(60)->block(120)
                ->then($call),
            null,
            fn (Throwable $e) => self::isWorthRetrying($e)
        );
    }

    public static function isWorthRetrying(Throwable $e): bool
    {
        if ($e instanceof ConnectException) {
            return true;
        }

        if (!$e instanceof GoogleServiceException) {
            return false;
        }

        $reasons = Arr::pluck($e->getErrors() ?? [], 'reason');

        return $e->getCode() === 429 || array_intersect($reasons, ['quotaExceeded', 'rateLimitExceeded', 'userRateLimitExceeded']) !== [];
    }

    private static function baseClient(): Client
    {
        $client = new Client();
        $client->setApplicationName('Aiku Google Tag Manager');
        $client->setClientId((string) config('services.google.client_id'));
        $client->setClientSecret((string) config('services.google.client_secret'));
        $client->addScope(TagManager::TAGMANAGER_READONLY);

        return $client;
    }
}
