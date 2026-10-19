<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\WebsiteVisitor;

use App\Models\CRM\WebUser;
use App\Models\Web\Website;
use App\Models\Web\WebsiteVisitor;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class RepairWebUserAnonymousVisitorLinks
{
    use AsAction;

    /**
     * @return array{web_users: int, linked_web_users: int, linked_visitors: int}
     */
    public function handle(Website $website, bool $dryRun = false): array
    {
        $result = ['web_users' => 0, 'linked_web_users' => 0, 'linked_visitors' => 0];

        WebUser::query()
            ->where('website_id', $website->id)
            ->where('is_root', true)
            ->whereHas('customer', fn ($customer) => $customer->whereNotNull('registered_at'))
            ->whereExists(
                fn ($visitors) => $visitors->selectRaw('1')
                    ->from('website_visitors')
                    ->whereColumn('website_visitors.web_user_id', 'web_users.id')
            )
            ->with('customer:id,registered_at')
            ->chunkById(500, function ($webUsers) use (&$result, $dryRun) {
                foreach ($webUsers as $webUser) {
                    $result['web_users']++;

                    $firstLoggedInVisitor = WebsiteVisitor::query()
                        ->where('web_user_id', $webUser->id)
                        ->orderBy('first_seen_at')
                        ->first();

                    $anonymousVisitors = LinkAnonymousVisitorsToWebUser::make()->anonymousVisitors(
                        $webUser,
                        $firstLoggedInVisitor->website_id,
                        $firstLoggedInVisitor->visitor_hash,
                        $firstLoggedInVisitor->id,
                        Carbon::parse($webUser->customer->registered_at)
                    );

                    $linkedVisitors = $dryRun ? $anonymousVisitors->count() : $anonymousVisitors->update(['web_user_id' => $webUser->id]);

                    if ($linkedVisitors > 0) {
                        $result['linked_web_users']++;
                        $result['linked_visitors'] += $linkedVisitors;
                    }
                }
            });

        return $result;
    }

    public function getCommandSignature(): string
    {
        return 'maintenance:repair_web_user_anonymous_visitor_links {website? : Website slug} {--dry-run : Count what would be linked without writing}';
    }

    public function asCommand(Command $command): int
    {
        $websites = Website::query()
            ->when($command->argument('website'), fn ($query, $slug) => $query->where('slug', $slug))
            ->get();

        $dryRun = (bool) $command->option('dry-run');

        try {
            foreach ($websites as $website) {
                $result = $this->handle($website, $dryRun);
                $action = $dryRun ? 'would link' : 'linked';

                $command->line("$website->slug: $action {$result['linked_visitors']} anonymous visits to {$result['linked_web_users']} of {$result['web_users']} web users");
            }
        } catch (Throwable $e) {
            $command->error($e->getMessage());

            return 1;
        }

        return 0;
    }
}
