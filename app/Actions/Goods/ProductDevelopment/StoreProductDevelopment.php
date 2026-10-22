<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-16h-55m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment;

use App\Actions\Goods\ProductDevelopment\Design\ProductDevelopmentArtworks;
use App\Actions\Goods\ProductDevelopment\Hydrators\ProductDevelopmentHydrateChecklist;
use App\Actions\Goods\TradeUnit\StoreTradeUnit;
use App\Actions\Goods\TradeUnit\UpdateTradeUnit;
use App\Actions\OrgAction;
use App\Actions\UI\Grp\BreakUserUiProps;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentChecklistLevelEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStageEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStageStateEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStatusEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentTypeEnum;
use App\Enums\Goods\TradeUnit\TradeUnitMarketEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentChecklistTemplate;
use App\Models\Goods\ProductDevelopmentItem;
use App\Models\Goods\ProductDevelopmentMember;
use App\Models\Goods\TradeUnit;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\User;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class StoreProductDevelopment extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    private ?User $creator = null;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $this->canCreateProductDevelopment($request->user());
    }

    public function handle(Group $group, User $creator, array $modelData): ProductDevelopment
    {
        $members = Arr::pull($modelData, 'members', []);
        $items   = Arr::pull($modelData, 'items', []);
        $brandId = Arr::pull($modelData, 'brand_id');

        if ($supplierId = Arr::get($modelData, 'supplier_id')) {
            $modelData['agent_id'] = Supplier::find($supplierId)?->agent_id;
        }

        return DB::transaction(function () use ($group, $creator, $modelData, $members, $items, $brandId) {
            $number = DB::selectOne('SELECT nextval(?) AS number', ['product_development_number_seq'])->number;

            /** @var ProductDevelopment $productDevelopment */
            $productDevelopment = ProductDevelopment::create([
                ...$modelData,
                'group_id'           => $group->id,
                'number'             => $number,
                'code'               => 'DEV-'.str_pad((string) $number, 4, '0', STR_PAD_LEFT),
                'owner_id'           => $modelData['owner_id'] ?? $creator->id,
                'checklist_category' => $modelData['checklist_category'] ?? SeedProductDevelopmentChecklistTemplates::DEFAULT_CATEGORY,
                'current_position'   => 1,
                'status'             => ProductDevelopmentStatusEnum::ACTIVE,
            ]);
            $productDevelopment->stats()->create();

            if ($brandId) {
                $productDevelopment->brands()->sync([$brandId]);
            }

            $this->createStages($productDevelopment);
            ProductDevelopmentArtworks::createDefaultSections($productDevelopment);

            $firstTimeMemberIds = collect($members)
                ->pluck('user_id')
                ->unique()
                ->reject(fn (int $userId) => ProductDevelopmentMember::where('user_id', $userId)->exists())
                ->all();

            foreach ($members as $member) {
                $productDevelopment->members()->firstOrCreate([
                    'user_id' => $member['user_id'],
                    'role'    => $member['role'],
                ]);
            }

            foreach (User::whereIn('id', $firstTimeMemberIds)->get() as $firstTimeMember) {
                BreakUserUiProps::dispatch($firstTimeMember)->afterCommit();
            }

            foreach (array_values($items) as $position => $item) {
                $tradeUnit = isset($item['trade_unit_id'])
                    ? TradeUnit::find($item['trade_unit_id'])
                    : $this->createTradeUnit($group, $productDevelopment, $item);

                $productDevelopment->items()->create([
                    'trade_unit_id' => $tradeUnit->id,
                    'position'      => $position,
                ]);
            }

            $this->recordCreationActivity($productDevelopment);
            $this->createChecklistItems($productDevelopment);
            ProductDevelopmentHydrateChecklist::run($productDevelopment);
            $this->createOpeningTasks($productDevelopment, $creator);

            return $productDevelopment;
        });
    }

    private function createOpeningTasks(ProductDevelopment $productDevelopment, User $creator): void
    {
        CreateProductDevelopmentTask::make()->ensureForDevelopment($productDevelopment, $creator);
    }

    private function recordCreationActivity(ProductDevelopment $productDevelopment): void
    {
        $productDevelopment->load(['items.tradeUnit', 'members.user']);

        if ($productDevelopment->items->isNotEmpty()) {
            RecordProductDevelopmentActivity::record($productDevelopment, RecordProductDevelopmentActivity::SKUS_ADDED, [], [
                'skus' => $productDevelopment->items->map(fn (ProductDevelopmentItem $item) => $item->tradeUnit->code)->values()->all(),
            ]);
        }

        if ($productDevelopment->members->isNotEmpty()) {
            $roleLabels = ProductDevelopmentMemberRoleEnum::labels();
            RecordProductDevelopmentActivity::record($productDevelopment, RecordProductDevelopmentActivity::MEMBERS_ADDED, [], [
                'members' => $productDevelopment->members
                    ->map(fn (ProductDevelopmentMember $member) => ($member->user?->contact_name ?? $member->user?->username).' ('.$roleLabels[$member->role->value].')')
                    ->values()
                    ->all(),
            ]);
        }
    }

    private function createStages(ProductDevelopment $productDevelopment): void
    {
        $positionDueDates = [];
        $cursor           = Carbon::today();

        foreach (range(1, ProductDevelopmentStageEnum::lastPosition()) as $position) {
            $stagesAtPosition = array_filter(ProductDevelopmentStageEnum::cases(), fn (ProductDevelopmentStageEnum $stage) => $stage->position() === $position);
            $longest          = max(array_map(fn (ProductDevelopmentStageEnum $stage) => $stage->defaultDurationDays(), $stagesAtPosition));
            $cursor           = $cursor->copy()->addDays($longest);
            $positionDueDates[$position] = $cursor;
        }

        foreach (ProductDevelopmentStageEnum::cases() as $stage) {
            $isFirst = $stage->position() === 1;
            $productDevelopment->stages()->create([
                'position'     => $stage->position(),
                'sub_position' => $stage->subPosition(),
                'stage'        => $stage,
                'role'         => $stage->role(),
                'state'        => $isFirst ? ProductDevelopmentStageStateEnum::IN_PROGRESS : ProductDevelopmentStageStateEnum::PENDING,
                'started_at'   => $isFirst ? now() : null,
                'due_at'       => $positionDueDates[$stage->position()],
            ]);
        }
    }

    private function createTradeUnit(Group $group, ProductDevelopment $productDevelopment, array $item): TradeUnit
    {
        $tradeUnit = StoreTradeUnit::make()->action($group, [
            'code' => $item['code'],
            'name' => $item['name'],
        ]);

        if ($productDevelopment->trade_unit_family_id) {
            $tradeUnit = UpdateTradeUnit::make()->action($tradeUnit, [
                'trade_unit_family_id' => $productDevelopment->trade_unit_family_id,
            ]);
        }

        return $tradeUnit;
    }

    private function createChecklistItems(ProductDevelopment $productDevelopment): void
    {
        $templatesQuery = ProductDevelopmentChecklistTemplate::where('group_id', $productDevelopment->group_id)
            ->where('category', $productDevelopment->checklist_category)
            ->where('is_active', true)
            ->orderBy('position');

        if ($productDevelopment->checklist_category === SeedProductDevelopmentChecklistTemplates::DEFAULT_CATEGORY && !$templatesQuery->clone()->exists()) {
            SeedProductDevelopmentChecklistTemplates::run($productDevelopment->group);
        }

        $templates = $templatesQuery->get();

        $productDevelopment->load('items');

        foreach ($templates as $template) {
            $targets = $template->level === ProductDevelopmentChecklistLevelEnum::FAMILY
                ? [null]
                : $productDevelopment->items->all();

            /** @var ProductDevelopmentItem|null $item */
            foreach ($targets as $item) {
                $productDevelopment->checklistItems()->create([
                    'product_development_item_id' => $item?->id,
                    'template_id'                 => $template->id,
                    'role'                        => $template->role,
                    'level'                       => $template->level,
                    'key'                         => $template->key,
                    'label'                       => $template->label,
                    'required'                    => $template->required,
                    'target_field'                => $template->target_field,
                ]);
            }
        }
    }

    public function rules(): array
    {
        $groupId = $this->group->id;

        return [
            'name'                 => ['required', 'string', 'max:255'],
            'type'                 => ['required', Rule::enum(ProductDevelopmentTypeEnum::class)],
            'brand_id'             => ['sometimes', 'nullable', 'integer', Rule::exists('brands', 'id')->where('group_id', $groupId)],
            'owner_id'             => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')->where('group_id', $groupId)->where('status', true)],
            'trade_unit_family_id' => ['sometimes', 'nullable', 'integer', Rule::exists('trade_unit_families', 'id')->where('group_id', $groupId)],
            'packaging_family_id'  => ['sometimes', 'nullable', 'integer', Rule::exists('packaging_families', 'id')->where('group_id', $groupId)],
            'supplier_id'          => ['sometimes', 'nullable', 'integer', Rule::exists('suppliers', 'id')->where('group_id', $groupId)],
            'agent_id'             => ['sometimes', 'nullable', 'integer', Rule::exists('agents', 'id')->where('group_id', $groupId)],
            'target_landing_date'  => ['sometimes', 'nullable', 'date'],
            'target_online_date'   => ['sometimes', 'nullable', 'date'],
            'brief'                => ['sometimes', 'nullable', 'string', 'max:20000'],
            'markets'              => ['sometimes', 'array'],
            'markets.*'            => [Rule::enum(TradeUnitMarketEnum::class)],
            'checklist_category'   => ['sometimes', 'nullable', 'string', 'max:64'],
            'members'              => ['sometimes', 'array', 'max:50'],
            'members.*.user_id'    => ['required', 'integer', Rule::exists('users', 'id')->where('group_id', $groupId)->where('status', true)],
            'members.*.role'       => ['required', Rule::enum(ProductDevelopmentMemberRoleEnum::class)],
            'items'                => ['sometimes', 'array', 'max:100'],
            'items.*.trade_unit_id' => ['sometimes', 'nullable', 'integer', 'distinct', Rule::exists('trade_units', 'id')->where('group_id', $groupId)],
            'items.*.code'         => [
                'required_without:items.*.trade_unit_id',
                'nullable',
                'distinct:ignore_case',
                ...self::skuCodeRules($groupId),
            ],
            'items.*.name'         => ['required_without:items.*.trade_unit_id', 'nullable', 'string', 'max:255'],
        ];
    }

    /**
     * What a new SKU code must pass, shared with the check the create form runs while typing.
     *
     * @return array<int, mixed>
     */
    public static function skuCodeRules(int $groupId): array
    {
        return [
            'max:64',
            new AlphaDashDot(),
            Rule::notIn(['export', 'create', 'upload']),
            new IUnique(table: 'trade_units', column: 'code', extraConditions: [['column' => 'group_id', 'value' => $groupId]], message: __('This code is already used by another trade unit')),
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'items.*.code.required_without' => __('Enter a code'),
            'items.*.code.distinct'         => __('This code is used twice in this form'),
            'items.*.code.max'              => __('The code can be at most 64 characters'),
            'items.*.code.not_in'           => __('This word cannot be used as a code'),
            'items.*.name.required_without' => __('Enter a name'),
        ];
    }

    public function getValidationAttributes(): array
    {
        return [
            'items.*.code' => __('code'),
            'items.*.name' => __('name'),
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        $landing = $this->get('target_landing_date');
        $online  = $this->get('target_online_date');
        if ($landing && $online && Carbon::parse($online)->lt(Carbon::parse($landing))) {
            $validator->errors()->add('target_online_date', __('The online date cannot be before the landing date'));
        }

        $supplierId = $this->get('supplier_id');
        $agentId    = $this->get('agent_id');
        if ($supplierId && $agentId && (int) Supplier::whereKey($supplierId)->value('agent_id') !== (int) $agentId) {
            $validator->errors()->add('agent_id', __('This supplier belongs to another agent'));
        }

        $agentId = $supplierId ? Supplier::whereKey($supplierId)->value('agent_id') : $agentId;
        $error   = $this->productDevelopmentAccess()->agentRoleError((array) $this->get('members', []), $agentId ? (int) $agentId : null, $this->group->id);
        if ($error) {
            $validator->errors()->add('members', $error);
        }
    }

    public function asController(ActionRequest $request): ProductDevelopment
    {
        $this->creator = $request->user();
        $this->initialisationFromGroup(group(), $request);

        return $this->handle($this->group, $this->creator, $this->validatedData);
    }

    public function action(Group $group, User $creator, array $modelData): ProductDevelopment
    {
        $this->asAction = true;
        $this->creator  = $creator;
        $this->initialisationFromGroup($group, $modelData);

        return $this->handle($group, $creator, $this->validatedData);
    }

    public function htmlResponse(ProductDevelopment $productDevelopment): RedirectResponse
    {
        return Redirect::route('grp.product_developments.show', ['productDevelopment' => $productDevelopment->slug]);
    }
}
