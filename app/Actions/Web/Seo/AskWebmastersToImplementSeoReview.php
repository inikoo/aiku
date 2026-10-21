<?php

/*
 * Created: Fri, 16 Oct 2026
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Seo;

use App\Actions\Chat\Staff\SendStaffMessage;
use App\Actions\Tasks\SendStaffTaskBadgeUpdateToUsers;
use App\Actions\Tasks\StoreStaffTask;
use App\Enums\Tasks\StaffTaskStatusEnum;
use App\Events\BroadcastStaffTaskChanged;
use App\Models\SysAdmin\User;
use App\Models\Tasks\StaffTask;
use App\Models\Web\Webpage;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Sending an SEO review gives the webmasters handling the page one task to implement it: the
 * webmaster who locked the page is assigned and the ones granted edit access work on it too.
 * Nobody handles an unlocked page, so its task waits in the webmaster department. Sending again
 * updates the open task, and validating the review closes it.
 */
class AskWebmastersToImplementSeoReview
{
    use AsAction;

    public const string TASK_KIND = 'seo_review';

    /**
     * @param array{model_type?: string, model_id?: int} $link
     */
    public function handle(Webpage $webpage, User $requester, int $suggestionsCount, string $reviewUrl, array $link = []): ?StaffTask
    {
        if ($suggestionsCount === 0) {
            return null;
        }

        $openTask = $this->openTaskFor($webpage);

        if ($openTask) {
            SendStaffMessage::run($openTask->conversation, $requester, [
                'body' => trans_choice(':user sent the SEO review again, :count suggestion is waiting and replaces the earlier ones.|:user sent the SEO review again, :count suggestions are waiting and replace the earlier ones.', $suggestionsCount, ['user' => $requester->chatName()]),
            ]);
            BroadcastStaffTaskChanged::dispatch($openTask);
            SendStaffTaskBadgeUpdateToUsers::run($openTask->involvedUserIds());

            return $openTask;
        }

        $webmasterIds = $this->webmasterIdsHandling($webpage, $requester);
        $assignment   = $webmasterIds
            ? ['assignee_id' => $webmasterIds[0], 'collaborator_ids' => array_slice($webmasterIds, 1)]
            : ['department' => 'webmaster'];

        return StoreStaffTask::run($requester, [
            'subject'     => __('Implement the SEO suggestions on :page', ['page' => $webpage->code]),
            'description' => trans_choice(':user sent :count SEO suggestion for :page. Implement it, then validate the review.|:user sent :count SEO suggestions for :page. Implement each one, then validate the review.', $suggestionsCount, [
                'user' => $requester->chatName(),
                'page' => $webpage->code,
            ])."\n\n".$reviewUrl,
            'data'        => ['kind' => self::TASK_KIND, 'webpage_id' => $webpage->id, 'shop_id' => $webpage->shop_id, 'organisation_id' => $webpage->organisation_id],
            ...$link,
            ...$assignment,
        ]);
    }

    public function close(Webpage $webpage): void
    {
        $task = $this->openTaskFor($webpage);

        if (!$task) {
            return;
        }

        $task->update(['status' => StaffTaskStatusEnum::DONE, 'closed_at' => now()]);

        BroadcastStaffTaskChanged::dispatch($task);
        SendStaffTaskBadgeUpdateToUsers::run($task->involvedUserIds());
    }

    /**
     * The lock owner first, so they are the assignee, then everyone whose edit grant is still valid.
     *
     * @return array<int, int>
     */
    private function webmasterIdsHandling(Webpage $webpage, User $requester): array
    {
        if (!$webpage->isLocked()) {
            return [];
        }

        $candidateIds = collect([$webpage->locked_by_user_id])
            ->merge(Arr::pluck(Arr::get($webpage->lock_data, 'editors', []), 'user_id'))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $webmasters = User::whereIn('id', $candidateIds)
            ->where('status', true)
            ->whereNot('id', $requester->id)
            ->get()
            ->filter(fn (User $user) => $webpage->canBeEditedBy($user) && StaffTask::canBeAssigned($user))
            ->keyBy('id');

        return $candidateIds->filter(fn (int $id) => $webmasters->has($id))->values()->all();
    }

    private function openTaskFor(Webpage $webpage): ?StaffTask
    {
        return StaffTask::open()
            ->where('data->kind', self::TASK_KIND)
            ->where('data->webpage_id', $webpage->id)
            ->first();
    }
}
