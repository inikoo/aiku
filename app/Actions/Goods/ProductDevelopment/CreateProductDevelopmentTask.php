<?php

/*
 * Author Louis Perez
 * Created on 16-10-2026-18h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment;

use App\Actions\Tasks\StoreStaffTask;
use App\Actions\Tasks\SyncStaffTaskCollaborators;
use App\Actions\Tasks\UpdateStaffTask;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStageEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStatusEnum;
use App\Enums\SysAdmin\User\UserNotificationEnum;
use App\Enums\Tasks\StaffTaskStatusEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\SysAdmin\User;
use App\Models\Tasks\StaffTask;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateProductDevelopmentTask
{
    use AsAction;

    public const string SPECS            = 'complete_specs';
    public const string COMPLIANCE_SCOPE = 'confirm_compliance_scope';
    public const string SEND_ARTWORK     = 'send_approved_artwork';
    public const string CONFIRM_ARTWORK  = 'confirm_artwork_received';

    public function handle(ProductDevelopment $productDevelopment, string $key, ?User $requester = null): ?StaffTask
    {
        $definition = $this->definition($productDevelopment, $key);
        $holders    = NotifyProductDevelopmentUsers::make()->usersWithRole($productDevelopment, $definition['role']);

        if ($holders->isEmpty() || $this->existing($productDevelopment, $key)) {
            return null;
        }

        $assignee  = $holders->first();
        $requester ??= $productDevelopment->owner ?? $assignee;

        $task = StoreStaffTask::run($requester, [
            'subject'     => $definition['subject'],
            'description' => $definition['description'],
            'assignee_id' => $assignee->id,
            'due_at'      => $definition['due_at']?->toDateString(),
            'model_type'  => 'ProductDevelopment',
            'model_id'    => $productDevelopment->id,
            'notify'      => false,
            'data'        => [
                'product_development_event' => $key,
                'product_development_role'  => $definition['role']->value,
            ],
        ]);

        $this->syncCollaborators($task, $holders, $requester);

        NotifyProductDevelopmentUsers::run(
            $productDevelopment,
            $holders,
            UserNotificationEnum::PRODUCT_DEVELOPMENT_TASK,
            __(':reference is for you', ['reference' => $task->reference]),
            $definition['subject'],
            url: route('grp.tasks.show', $task->reference),
            except: $requester,
        );

        return $task;
    }

    /**
     * Creates the tasks a development is due now, moves an open task to someone holding its role
     * when it sits with someone who does not, and keeps every other role holder as a collaborator.
     */
    public function ensureForDevelopment(ProductDevelopment $productDevelopment, User $actor): void
    {
        $notifier = NotifyProductDevelopmentUsers::make();

        foreach ([self::SPECS, self::COMPLIANCE_SCOPE, self::SEND_ARTWORK, self::CONFIRM_ARTWORK] as $key) {
            if (!$this->isDue($productDevelopment, $key)) {
                continue;
            }

            $task = $this->existing($productDevelopment, $key);
            if (!$task) {
                $this->handle($productDevelopment, $key, $actor);
                continue;
            }

            if (!$task->status->isOpen()) {
                continue;
            }

            $holders = $notifier->usersWithRole($productDevelopment, $this->definition($productDevelopment, $key)['role']);
            if ($holders->isEmpty()) {
                continue;
            }

            if (!$holders->contains('id', $task->assignee_id)) {
                $task = UpdateStaffTask::run($task, $actor, ['assignee_id' => $holders->first()->id]);
            }

            $this->syncCollaborators($task, $holders, $actor);
        }
    }

    private function isDue(ProductDevelopment $productDevelopment, string $key): bool
    {
        if ($productDevelopment->status !== ProductDevelopmentStatusEnum::ACTIVE) {
            return false;
        }

        $artworkPosition = ProductDevelopmentStageEnum::ARTWORK_TO_SUPPLIER->position();

        return match ($key) {
            self::SPECS, self::COMPLIANCE_SCOPE => $productDevelopment->current_position <= ProductDevelopmentStageEnum::PACK_CONTENT->position(),
            self::SEND_ARTWORK => $productDevelopment->current_position === $artworkPosition,
            self::CONFIRM_ARTWORK => $productDevelopment->current_position === $artworkPosition
                && $this->existing($productDevelopment, self::SEND_ARTWORK)?->status === StaffTaskStatusEnum::DONE,
            default => false,
        };
    }

    private function existing(ProductDevelopment $productDevelopment, string $key): ?StaffTask
    {
        return StaffTask::query()
            ->where('model_type', 'ProductDevelopment')
            ->where('model_id', $productDevelopment->id)
            ->where('data->product_development_event', $key)
            ->where('status', '!=', StaffTaskStatusEnum::CANCELLED->value)
            ->latest('id')
            ->first();
    }

    /**
     * @param Collection<int, User> $holders
     */
    private function syncCollaborators(StaffTask $task, Collection $holders, User $actor): void
    {
        $others = $holders->pluck('id')->reject(fn (int $id) => $id === $task->assignee_id)->values();
        $wanted = $task->collaborators()->pluck('users.id')->map(fn ($id) => (int) $id)->merge($others)->unique()->values()->all();

        if (array_diff($wanted, $task->collaborators()->pluck('users.id')->map(fn ($id) => (int) $id)->all())) {
            SyncStaffTaskCollaborators::run($task, $wanted, $actor);
        }
    }

    /**
     * @return array{role: ProductDevelopmentMemberRoleEnum, subject: string, description: string, due_at: Carbon|null}
     */
    private function definition(ProductDevelopment $productDevelopment, string $key): array
    {
        $stageDue = function (ProductDevelopmentStageEnum $stage) use ($productDevelopment): ?Carbon {
            $date = $productDevelopment->stages()->where('stage', $stage->value)->value('due_at');

            return $date ? Carbon::parse($date) : null;
        };
        $family = $productDevelopment->name;
        $code   = $productDevelopment->code;

        return match ($key) {
            self::SPECS => [
                'role'        => ProductDevelopmentMemberRoleEnum::AGENT,
                'subject'     => __('Complete specs for :family', ['family' => $family]),
                'description' => __('Fill in the product specs of every SKU in :code (sizes, weights, cartons, country of origin) and upload the factory photos.', ['code' => $code]),
                'due_at'      => $stageDue(ProductDevelopmentStageEnum::PRODUCT_SPECS),
            ],
            self::COMPLIANCE_SCOPE => [
                'role'        => ProductDevelopmentMemberRoleEnum::COMPLIANCE,
                'subject'     => __('Confirm category and required documents for :family', ['family' => $family]),
                'description' => __('Check the product category of :code and which documents (SDS, IFRA, CPSR, test reports) each SKU needs.', ['code' => $code]),
                'due_at'      => $stageDue(ProductDevelopmentStageEnum::PACK_CONTENT),
            ],
            self::SEND_ARTWORK => [
                'role'        => ProductDevelopmentMemberRoleEnum::PURCHASING,
                'subject'     => __('Send approved artwork for :family', ['family' => $family]),
                'description' => __('The artwork of :code is approved and locked: send it to the supplier, then mark this task done.', ['code' => $code]),
                'due_at'      => $stageDue(ProductDevelopmentStageEnum::ARTWORK_TO_SUPPLIER),
            ],
            self::CONFIRM_ARTWORK => [
                'role'        => ProductDevelopmentMemberRoleEnum::AGENT,
                'subject'     => __('Confirm factory received artwork for :family', ['family' => $family]),
                'description' => __('Purchasing sent the approved artwork of :code to the supplier: confirm the factory has received it.', ['code' => $code]),
                'due_at'      => Carbon::today()->addDays(2),
            ],
        };
    }
}
