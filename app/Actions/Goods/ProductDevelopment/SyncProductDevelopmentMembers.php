<?php

/*
 * Author Louis Perez
 * Created on 16-10-2026-11h-30m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Actions\UI\Grp\BreakUserUiProps;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentMember;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class SyncProductDevelopmentMembers extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    private ?ProductDevelopment $productDevelopment = null;

    private function target(): ProductDevelopment
    {
        return $this->productDevelopment ?? request()->route('productDevelopment');
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $this->canViewProductDevelopment($request->user(), $this->target())
            && $this->canEditProductDevelopment($request->user(), $this->target());
    }

    /**
     * @param array<int, array{user_id: int, role: string}> $members
     */
    public function handle(ProductDevelopment $productDevelopment, array $members): ProductDevelopment
    {
        $wanted = collect($members)
            ->map(fn (array $member) => ['user_id' => (int) $member['user_id'], 'role' => $member['role']])
            ->unique(fn (array $member) => $member['user_id'].'|'.$member['role'])
            ->values();

        return DB::transaction(function () use ($productDevelopment, $wanted) {
            $current = $productDevelopment->members()->with('user')->get();
            $key     = fn (int $userId, string $role) => $userId.'|'.$role;

            $currentKeys = $current->map(fn (ProductDevelopmentMember $member) => $key($member->user_id, $member->role->value));
            $wantedKeys  = $wanted->map(fn (array $member) => $key($member['user_id'], $member['role']));

            $removed = $current->filter(fn (ProductDevelopmentMember $member) => !$wantedKeys->contains($key($member->user_id, $member->role->value)));
            $added   = $wanted->filter(fn (array $member) => !$currentKeys->contains($key($member['user_id'], $member['role'])));

            $firstTimeMemberIds = $added->pluck('user_id')->unique()
                ->reject(fn (int $userId) => ProductDevelopmentMember::where('user_id', $userId)->exists())
                ->values();

            ProductDevelopmentMember::whereIn('id', $removed->pluck('id'))->delete();

            foreach ($added as $member) {
                $productDevelopment->members()->create($member);
            }

            $roleLabels = ProductDevelopmentMemberRoleEnum::labels();
            $users      = User::whereIn('id', $added->pluck('user_id'))->get()->keyBy('id');

            if ($added->isNotEmpty()) {
                RecordProductDevelopmentActivity::record($productDevelopment, RecordProductDevelopmentActivity::MEMBERS_ADDED, [], [
                    'members' => $this->describe($added->map(fn (array $member) => [$users->get($member['user_id']), $member['role']]), $roleLabels),
                ]);
            }

            if ($removed->isNotEmpty()) {
                RecordProductDevelopmentActivity::record($productDevelopment, RecordProductDevelopmentActivity::MEMBERS_REMOVED, [
                    'members' => $this->describe($removed->map(fn (ProductDevelopmentMember $member) => [$member->user, $member->role->value]), $roleLabels),
                ], []);
            }

            foreach (User::whereIn('id', $firstTimeMemberIds)->get() as $firstTimeMember) {
                BreakUserUiProps::dispatch($firstTimeMember)->afterCommit();
            }

            return $productDevelopment->refresh();
        });
    }

    /**
     * @param Collection<int, array{0: User|null, 1: string}> $pairs
     * @param array<string, string> $roleLabels
     *
     * @return array<int, string>
     */
    private function describe(Collection $pairs, array $roleLabels): array
    {
        return $pairs
            ->map(fn (array $pair) => ($pair[0]?->contact_name ?: $pair[0]?->username).' ('.($roleLabels[$pair[1]] ?? $pair[1]).')')
            ->values()
            ->all();
    }

    public function rules(): array
    {
        $groupId = $this->target()->group_id;

        return [
            'members'           => ['present', 'array', 'max:50'],
            'members.*.user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('group_id', $groupId)->where('status', true)],
            'members.*.role'    => ['required', Rule::enum(ProductDevelopmentMemberRoleEnum::class)],
        ];
    }

    public function asController(ProductDevelopment $productDevelopment, ActionRequest $request): ProductDevelopment
    {
        $this->productDevelopment = $productDevelopment;
        $this->initialisationFromGroup($productDevelopment->group, $request);

        return $this->handle($productDevelopment, $this->validatedData['members'] ?? []);
    }

    public function action(ProductDevelopment $productDevelopment, array $modelData): ProductDevelopment
    {
        $this->asAction           = true;
        $this->productDevelopment = $productDevelopment;
        $this->initialisationFromGroup($productDevelopment->group, $modelData);

        return $this->handle($productDevelopment, $this->validatedData['members'] ?? []);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
