<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\GoogleTagManager;

use App\Models\SysAdmin\Group;
use App\Models\Web\GoogleTagManagerContainer;
use App\Services\GoogleTagManager\GoogleTagManagerClient;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Mirrors every Tag Manager container the group's Google login can see into `google_tag_manager_containers`,
 * and removes the ones that login no longer sees, so the page never has to call Google.
 */
class FetchGoogleTagManagerContainers
{
    use AsAction;

    public string $commandSignature   = 'google_tag_manager:fetch';
    public string $commandDescription = 'Fetch the Google Tag Manager accounts and containers of the group';

    public function handle(Group $group): int
    {
        $client = GoogleTagManagerClient::forGroup($group);

        if (!$client) {
            return 0;
        }

        $fetchedAt = now();
        $rows      = $client->containers();

        foreach ($rows as $row) {
            GoogleTagManagerContainer::updateOrCreate(
                [
                    'group_id'     => $group->id,
                    'account_id'   => $row['account_id'],
                    'container_id' => $row['container_id'],
                ],
                [...$row, 'fetched_at' => $fetchedAt]
            );
        }

        GoogleTagManagerContainer::where('group_id', $group->id)->where('fetched_at', '<', $fetchedAt)->delete();

        return count($rows);
    }

    public function asCommand(Command $command): int
    {
        $group = group();

        if (!GoogleTagManagerClient::isConnected($group)) {
            $command->error('Google Tag Manager is not connected for this group.');

            return 1;
        }

        $command->info($this->handle($group).' containers fetched');

        return 0;
    }
}
