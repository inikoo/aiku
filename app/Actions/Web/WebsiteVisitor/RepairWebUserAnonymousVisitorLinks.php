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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class RepairWebUserAnonymousVisitorLinks
{
    use AsAction;

    public const int CHUNK_SIZE = 500;

    public string $jobConnection = 'redis-long-running';
    public string $jobQueue      = 'long-running';
    public int $jobTimeout       = 1800;

    /**
     * Links the anonymous visits of the website's customers inline and reports what was done, or
     * what would be done. One command run reads every web user of the website, which is minutes on
     * a large one: the command hands that to Horizon by default, see dispatchForWebsite.
     *
     * @return array{web_users: int, linked_web_users: int, linked_visitors: int}
     */
    public function handle(Website $website, bool $dryRun = false): array
    {
        $result = ['web_users' => 0, 'linked_web_users' => 0, 'linked_visitors' => 0];

        $this->webUsersToRepair($website)
            ->chunkById(self::CHUNK_SIZE, function ($webUsers) use (&$result, $dryRun) {
                $this->repairWebUsers($webUsers, $dryRun, $result);
            });

        return $result;
    }

    /**
     * One job per chunk of web users on the long-running lane, so a failed chunk is retried on its
     * own and the chunks run side by side. Linking is idempotent: it only touches visits that are
     * still anonymous, so a retried or repeated chunk changes nothing it already did.
     *
     * @return array{web_users: int, jobs: int}
     */
    public function dispatchForWebsite(Website $website): array
    {
        $dispatched = ['web_users' => 0, 'jobs' => 0];

        $this->webUsersToRepair($website)
            ->setEagerLoads([])
            ->select('web_users.id')
            ->chunkById(self::CHUNK_SIZE, function ($webUsers) use (&$dispatched) {
                self::dispatch($webUsers->pluck('id')->all());

                $dispatched['web_users'] += $webUsers->count();
                $dispatched['jobs']++;
            });

        return $dispatched;
    }

    /**
     * @param array<int, int> $webUserIds
     */
    public function asJob(array $webUserIds): void
    {
        $result = ['web_users' => 0, 'linked_web_users' => 0, 'linked_visitors' => 0];

        $webUsers = WebUser::query()
            ->whereIn('id', $webUserIds)
            ->with('customer:id,registered_at')
            ->get();

        $this->repairWebUsers($webUsers, false, $result);
    }

    /**
     * @return Builder<WebUser>
     */
    private function webUsersToRepair(Website $website): Builder
    {
        return WebUser::query()
            ->where('website_id', $website->id)
            ->where('is_root', true)
            ->whereHas('customer', fn ($customer) => $customer->whereNotNull('registered_at'))
            ->whereExists(
                fn ($visitors) => $visitors->selectRaw('1')
                    ->from('website_visitors')
                    ->whereColumn('website_visitors.web_user_id', 'web_users.id')
            )
            ->with('customer:id,registered_at');
    }

    /**
     * @param Collection<int, WebUser>                                                     $webUsers
     * @param array{web_users: int, linked_web_users: int, linked_visitors: int}          $result
     */
    private function repairWebUsers(Collection $webUsers, bool $dryRun, array &$result): void
    {
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
                Carbon::parse($webUser->customer->registered_at),
                $firstLoggedInVisitor->visitor_uuid
            );

            $linkedVisitors = $dryRun ? $anonymousVisitors->count() : $anonymousVisitors->update(['web_user_id' => $webUser->id]);

            if ($linkedVisitors > 0) {
                $result['linked_web_users']++;
                $result['linked_visitors'] += $linkedVisitors;
            }
        }
    }

    public function getCommandSignature(): string
    {
        return 'maintenance:repair_web_user_anonymous_visitor_links {website? : Website slug} {--dry-run : Count what would be linked without writing} {--sync : Run inline instead of dispatching Horizon jobs}';
    }

    public function asCommand(Command $command): int
    {
        $websites = Website::query()
            ->when($command->argument('website'), fn ($query, $slug) => $query->where('slug', $slug))
            ->get();

        $dryRun = (bool) $command->option('dry-run');
        $inline = $dryRun || $command->option('sync');

        try {
            foreach ($websites as $website) {
                if (! $inline) {
                    $dispatched = $this->dispatchForWebsite($website);
                    $command->line("$website->slug: {$dispatched['jobs']} jobs dispatched to Horizon for {$dispatched['web_users']} web users");

                    continue;
                }

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
