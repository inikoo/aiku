<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Mon, 12 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\CRM\Customer;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCustomers;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCustomersDashboard;
use App\Actions\Catalogue\ShopPlatformStats\ShopPlatformStatsHydrateCustomers;
use App\Actions\Dropshipping\Platform\Hydrators\PlatformHydrateCustomers;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateCustomers;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateCustomers;
use App\Enums\CRM\Customer\CustomerStateEnum;
use App\Enums\CRM\Customer\CustomerStatusEnum;
use App\Models\Catalogue\Shop;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class RecalculateCustomerStates
{
    use AsAction;

    public string $commandSignature = 'crm:customers:recalculate-states {shop? : shop slug}';

    /**
     * @return array<string, int>
     */
    public function handle(Shop $shop): array
    {
        $rule = GetCustomerStateByLastInvoice::make();

        if (!$rule->appliesToShop($shop)) {
            return [];
        }

        $activeSince = $rule->activeSince($shop);
        $losingSince = $rule->losingSince($shop);

        $moved = [
            CustomerStateEnum::REGISTERED->value => $this->moveTo($shop, CustomerStateEnum::REGISTERED, fn (Builder $query) => $query->whereNull('last_invoiced_at')),
            CustomerStateEnum::ACTIVE->value     => $this->moveTo($shop, CustomerStateEnum::ACTIVE, fn (Builder $query) => $query->where('last_invoiced_at', '>=', $activeSince)),
            CustomerStateEnum::LOSING->value     => $this->moveTo($shop, CustomerStateEnum::LOSING, fn (Builder $query) => $query->where('last_invoiced_at', '<', $activeSince)->where('last_invoiced_at', '>=', $losingSince)),
            CustomerStateEnum::LOST->value       => $this->moveTo($shop, CustomerStateEnum::LOST, fn (Builder $query) => $query->where('last_invoiced_at', '<', $losingSince)),
        ];

        if (array_sum($moved) > 0) {
            $this->refreshStats($shop);
        }

        return $moved;
    }

    protected function moveTo(Shop $shop, CustomerStateEnum $state, callable $matchesRule): int
    {
        return DB::table('customers')
            ->where('shop_id', $shop->id)
            ->whereNull('deleted_at')
            ->where('status', CustomerStatusEnum::APPROVED->value)
            ->whereIn('state', GetCustomerStateByLastInvoice::RECALCULATED_STATES)
            ->where('state', '!=', $state->value)
            ->where($matchesRule)
            ->update(['state' => $state->value]);
    }

    protected function refreshStats(Shop $shop): void
    {
        ShopHydrateCustomers::dispatch($shop);
        ShopHydrateCustomersDashboard::dispatch($shop);
        OrganisationHydrateCustomers::dispatch($shop->organisation);
        GroupHydrateCustomers::dispatch($shop->group);

        foreach ($shop->platformStats as $platformStat) {
            ShopPlatformStatsHydrateCustomers::dispatch($shop, $platformStat->platform);
            PlatformHydrateCustomers::dispatch($platformStat->platform);
        }
    }

    public function asCommand(Command $command): int
    {
        $shops = Shop::query()
            ->where('is_aiku', true)
            ->when($command->argument('shop'), fn ($query, $slug) => $query->where('slug', $slug))
            ->orderBy('id')
            ->get();

        if ($shops->isEmpty()) {
            $command->error('No Aiku shop found');

            return 1;
        }

        $rows = [];
        foreach ($shops as $shop) {
            $moved = $this->handle($shop);

            if ($moved) {
                $rows[] = [$shop->slug, ...array_values($moved)];
            }
        }

        $command->table(['Shop', 'To registered', 'To active', 'To potential comebacks', 'To dormant'], $rows);

        return 0;
    }
}
