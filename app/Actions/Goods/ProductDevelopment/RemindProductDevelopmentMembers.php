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

        foreach ((new ProductDevelopmentWaitingOn())->byRole($productDevelopment) as $role => $rows) {
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
            );
            $reminded[] = $roleLabels[$role];
        }

        $data                       = $productDevelopment->data ?? [];
        $data['reminders_sent']     = ['at' => now()->toIso8601String(), 'by' => $sender->contact_name ?: $sender->username, 'roles' => $reminded];
        $productDevelopment->updateQuietly(['data' => $data]);

        RecordProductDevelopmentActivity::record($productDevelopment, RecordProductDevelopmentActivity::REMINDERS_SENT, [], ['roles' => $reminded]);

        return $reminded;
    }

    public function afterValidator(Validator $validator): void
    {
        if ((new ProductDevelopmentWaitingOn())->byRole($this->productDevelopment) === []) {
            $validator->errors()->add('reminders', __('Nothing is waiting on anyone'));
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
