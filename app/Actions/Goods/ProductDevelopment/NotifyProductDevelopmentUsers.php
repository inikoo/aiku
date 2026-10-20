<?php

/*
 * Author Louis Perez
 * Created on 16-10-2026-18h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment;

use App\Actions\SysAdmin\User\NotifyUserOnChosenChannels;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Enums\SysAdmin\User\UserNotificationEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\SysAdmin\User;
use App\Notifications\ProductDevelopmentNotification;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class NotifyProductDevelopmentUsers
{
    use AsAction;

    /**
     * @param iterable<User> $users
     */
    public function handle(?ProductDevelopment $productDevelopment, iterable $users, UserNotificationEnum $event, string $title, string $body, ?string $tab = null, ?string $url = null, ?User $except = null): void
    {
        foreach (collect($users)->filter()->unique('id') as $user) {
            if ($except && $user->id === $except->id) {
                continue;
            }
            $notification = new ProductDevelopmentNotification($productDevelopment, $title, $body, $tab, $url);
            NotifyUserOnChosenChannels::run($user, $event, $notification, $title, $body, $notification->link());
        }
    }

    /**
     * @return Collection<int, User>
     */
    public function usersWithRole(ProductDevelopment $productDevelopment, ProductDevelopmentMemberRoleEnum $role): Collection
    {
        return $productDevelopment->members()
            ->where('role', $role->value)
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter(fn (?User $user) => $user?->status)
            ->values();
    }
}
