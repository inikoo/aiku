<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-16h-55m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment;

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

        if (!Arr::get($modelData, 'agent_id') && $supplierId = Arr::get($modelData, 'supplier_id')) {
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

            $this->createChecklistItems($productDevelopment);
            ProductDevelopmentHydrateChecklist::run($productDevelopment);

            return $productDevelopment;
        });
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
                'max:64',
                'distinct:ignore_case',
                new AlphaDashDot(),
                Rule::notIn(['export', 'create', 'upload']),
                new IUnique(table: 'trade_units', column: 'code', extraConditions: [['column' => 'group_id', 'value' => $groupId]]),
            ],
            'items.*.name'         => ['required_without:items.*.trade_unit_id', 'nullable', 'string', 'max:255'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        $landing = $this->get('target_landing_date');
        $online  = $this->get('target_online_date');
        if ($landing && $online && Carbon::parse($online)->lt(Carbon::parse($landing))) {
            $validator->errors()->add('target_online_date', __('The online date cannot be before the landing date'));
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
