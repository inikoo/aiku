<?php

/*
 * Author Louis Perez
 * Created on 17-10-2026-22h-50m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Enums\SysAdmin\User\UserNotificationEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * "Send reminders" on the development page: every role the development is waiting on gets, on the
 * channels each person chose, the list of what is still waiting on them.
 */
class RemindProductDevelopmentMembers extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    public const int MAX_LINES = 8;

    private ProductDevelopment $productDevelopment;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $productDevelopment = $request->route('productDevelopment');

        return $this->canViewProductDevelopment($request->user(), $productDevelopment)
            && $this->productDevelopmentAccess()->canEdit($request->user(), $productDevelopment);
    }

    /**
     * @return array<int, string> the roles reminded
     */
    public function handle(ProductDevelopment $productDevelopment, User $sender): array
    {
        $notifier   = NotifyProductDevelopmentUsers::make();
        $roleLabels = ProductDevelopmentMemberRoleEnum::labels();
        $reminded   = [];
        $remindedKeys = [];
        $remindedUsers = collect();

        foreach (self::remindable($productDevelopment) as $role => $rows) {
            $recipients = $notifier->usersWithRole($productDevelopment, ProductDevelopmentMemberRoleEnum::from($role))->reject(fn (User $user) => $user->id === $sender->id);
            if ($recipients->isEmpty()) {
                continue;
            }

            $lines = collect($rows)->map(fn (array $row) => '• '.$row['label'].($row['detail'] ? ' ('.$row['detail'].')' : ''));
            $body  = $lines->take(self::MAX_LINES)->implode("\n").($lines->count() > self::MAX_LINES ? "\n".__('and :count more', ['count' => $lines->count() - self::MAX_LINES]) : '');

            $notifier->handle(
                $productDevelopment,
                $recipients,
                UserNotificationEnum::PRODUCT_DEVELOPMENT_TASK,
                __(':name is waiting on you for :code', ['name' => $sender->contact_name ?: $sender->username, 'code' => $productDevelopment->code]),
                $body,
                null,
                self::openAt($rows[0]),
            );
            $reminded[]     = $roleLabels[$role];
            $remindedKeys[] = $role;
            $remindedUsers = $remindedUsers->merge($recipients);
        }

        $remindedUsers->unique('id')->each(fn (User $user) => ProductDevelopmentWaitingOn::markReminded($user));

        $data                       = $productDevelopment->data ?? [];
        $data['reminders_sent']     = ['at' => now()->toIso8601String(), 'by' => $sender->contact_name ?: $sender->username, 'roles' => $reminded, 'role_keys' => $remindedKeys];
        $productDevelopment->updateQuietly(['data' => $data]);

        RecordProductDevelopmentActivity::record($productDevelopment, RecordProductDevelopmentActivity::REMINDERS_SENT, [], ['roles' => $reminded]);

        return $reminded;
    }

    /**
     * What a reminder may chase, by role: checklist items only for the roles whose stage is the current
     * one, as the scheduled reminders do, so nobody is nagged about a stage that has not started. Design,
     * review and sign-off rows already only appear once their round or sign-off is open.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function remindable(ProductDevelopment $productDevelopment): array
    {
        $currentRoles = $productDevelopment->stages()
            ->where('position', $productDevelopment->current_position)
            ->get()
            ->map(fn ($stage) => $stage->role->value)
            ->unique()
            ->all();

        return collect((new ProductDevelopmentWaitingOn())->byRole($productDevelopment))
            ->map(fn (array $rows, string $role) => array_values(array_filter($rows, fn (array $row) => $row['kind'] !== ProductDevelopmentWaitingOn::KIND_CHECKLIST || in_array($role, $currentRoles, true))))
            ->filter()
            ->all();
    }

    /**
     * Where a reminder opens: the page of the first thing waiting, and for a checklist item its tab with
     * that field brought into view.
     *
     * @param array<string, mixed> $row
     */
    public static function openAt(array $row): string
    {
        $parameters = $row['route']['parameters'];
        if ($row['kind'] === ProductDevelopmentWaitingOn::KIND_CHECKLIST && !empty($row['items'])) {
            $parameters['focus'] = 'pd-check-'.$row['items'][0];
        }

        return route($row['route']['name'], $parameters);
    }

    public function afterValidator(Validator $validator): void
    {
        if (self::remindable($this->productDevelopment) === []) {
            $validator->errors()->add('reminders', __('Nothing is waiting on anyone at this stage'));
        }
    }

    public function asController(ProductDevelopment $productDevelopment, ActionRequest $request): array
    {
        $this->productDevelopment = $productDevelopment;
        $this->initialisationFromGroup($productDevelopment->group, $request);

        return $this->handle($productDevelopment, $request->user());
    }

    public function action(ProductDevelopment $productDevelopment, User $sender): array
    {
        $this->asAction           = true;
        $this->productDevelopment = $productDevelopment;
        $this->initialisationFromGroup($productDevelopment->group, []);

        return $this->handle($productDevelopment, $sender);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
