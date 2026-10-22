<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-17h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

/** @noinspection PhpUnhandledExceptionInspection */

use App\Actions\Goods\ProductDevelopment\CreateProductDevelopmentTask;
use App\Actions\Goods\ProductDevelopment\GetProductDevelopmentStageGate;
use App\Actions\Goods\ProductDevelopment\SendProductDevelopmentReminders;
use App\Actions\Goods\ProductDevelopment\Hydrators\ProductDevelopmentHydrateChecklist;
use App\Actions\Goods\ProductDevelopment\MoveProductDevelopmentToNextStage;
use App\Actions\Goods\ProductDevelopment\LinkProductDevelopmentSupplierProduct;
use App\Actions\Goods\ProductDevelopment\StoreProductDevelopment;
use App\Actions\Goods\ProductDevelopment\StoreProductDevelopmentComment;
use App\Actions\Goods\ProductDevelopment\Design\ReviewProductDevelopmentDesignVersion;
use App\Actions\Goods\ProductDevelopment\Design\StoreProductDevelopmentDesignVersion;
use App\Actions\Goods\ProductDevelopment\Design\SubmitProductDevelopmentDesignVersion;
use App\Actions\Goods\ProductDevelopment\Design\UpdateProductDevelopmentDesignVersion;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundStatusEnum;
use App\Actions\Goods\ProductDevelopment\UpdateProductDevelopmentChecklistItem;
use App\Actions\Goods\ProductDevelopment\SyncProductDevelopmentMembers;
use App\Actions\Goods\ProductDevelopment\UploadProductDevelopmentFiles;
use App\Actions\Goods\ProductDevelopment\UI\GetProductDevelopmentActivity;
use App\Actions\Goods\ProductDevelopment\UI\ShowProductDevelopmentsBoard;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentChecklistItemStateEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStageEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStageStateEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStatusEnum;
use App\Enums\Goods\TradeUnit\TradeUnitStatusEnum;
use App\Models\Goods\ProductDevelopment;
use App\Actions\UI\Grp\Layout\GetGroupNavigation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {
    $this->group      = createGroup();
    $this->adminGuest = createAdminGuest($this->group);
    $this->user       = $this->adminGuest->getUser();
    Config::set('inertia.testing.page_paths', [resource_path('js/Pages/Grp')]);
    actingAs($this->user);
});

function testAgent(object $test): \App\Models\SupplyChain\Agent
{
    return \App\Models\SupplyChain\Agent::where('group_id', $test->group->id)->orderBy('id')->first()
        ?? \App\Actions\SupplyChain\Agent\StoreAgent::make()->action($test->group, \App\Models\SupplyChain\Agent::factory()->definition());
}

function newAgentUser(object $test, ?\App\Models\SupplyChain\Agent $agent = null): \App\Models\SysAdmin\User
{
    $agent ??= testAgent($test);
    $user    = newGuestUser($test);
    \Illuminate\Support\Facades\DB::table('user_has_authorised_models')->insert([
        'user_id'    => $user->id,
        'org_id'     => $agent->organisation_id,
        'model_type' => 'Organisation',
        'model_id'   => $agent->organisation_id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $user;
}

function storeDevelopmentWithNewSkus(object $test, int $numberSkus = 4): ProductDevelopment
{
    $suffix = Str::upper(Str::random(5));

    return StoreProductDevelopment::make()->action($test->group, $test->user, [
        'name'     => 'Lavender candles '.$suffix,
        'type'     => 'new',
        'agent_id' => testAgent($test)->id,
        'members' => [
            ['user_id' => $test->user->id, 'role' => ProductDevelopmentMemberRoleEnum::BUYER->value],
            ['user_id' => $test->user->id, 'role' => ProductDevelopmentMemberRoleEnum::APPROVER->value],
        ],
        'items'   => collect(range(1, $numberSkus))->map(fn (int $index) => [
            'code' => 'PD-'.$suffix.'-'.$index,
            'name' => 'Lavender candle '.$index,
        ])->all(),
    ]);
}

test('a buyer creates a development with 4 new SKUs and members', function () {
    $developmentsBefore = ProductDevelopment::count();

    $productDevelopment = storeDevelopmentWithNewSkus($this);

    expect(ProductDevelopment::count())->toBe($developmentsBefore + 1)
        ->and($productDevelopment->code)->toBe('DEV-'.str_pad((string) $productDevelopment->number, 4, '0', STR_PAD_LEFT))
        ->and($productDevelopment->owner_id)->toBe($this->user->id)
        ->and($productDevelopment->current_position)->toBe(1)
        ->and($productDevelopment->items)->toHaveCount(4)
        ->and($productDevelopment->members)->toHaveCount(2)
        ->and($productDevelopment->stages)->toHaveCount(count(ProductDevelopmentStageEnum::cases()));

    $productDevelopment->items->each(function ($item) {
        expect($item->tradeUnit->status)->toBe(TradeUnitStatusEnum::IN_PROCESS);
    });

    $brief = $productDevelopment->stages->firstWhere('stage', ProductDevelopmentStageEnum::BRIEF);
    expect($brief->state)->toBe(ProductDevelopmentStageStateEnum::IN_PROGRESS)
        ->and($brief->started_at)->not->toBeNull()
        ->and($productDevelopment->stages->where('position', 2))->toHaveCount(3);

    $stats = $productDevelopment->stats->fresh();
    expect($stats->number_items)->toBe(4)
        ->and($stats->number_checklist_items)->toBeGreaterThan(0)
        ->and($stats->number_required_missing)->toBe(
            $productDevelopment->checklistItems()->where('required', true)->where('state', ProductDevelopmentChecklistItemStateEnum::MISSING->value)->count()
        );

    return $productDevelopment;
});

test('stage move is blocked with a reason while required items are missing', function (ProductDevelopment $productDevelopment) {
    $gate = GetProductDevelopmentStageGate::run($productDevelopment);

    expect($gate['can_move'])->toBeFalse()
        ->and($gate['reason'])->toContain(__('Buyer'))
        ->and($gate['missing'])->toHaveKey(ProductDevelopmentMemberRoleEnum::BUYER->value);

    expect(fn () => MoveProductDevelopmentToNextStage::make()->action($productDevelopment, $this->user))
        ->toThrow(ValidationException::class);

    expect($productDevelopment->fresh()->current_position)->toBe(1);
})->depends('a buyer creates a development with 4 new SKUs and members');

test('stage moves once the gated items are complete or N/A', function (ProductDevelopment $productDevelopment) {
    $buyerItems = $productDevelopment->checklistItems()->where('role', ProductDevelopmentMemberRoleEnum::BUYER->value)->get();
    $buyerItems->first()->update(['state' => ProductDevelopmentChecklistItemStateEnum::COMPLETE]);
    $buyerItems->skip(1)->each(fn ($item) => $item->update(['state' => ProductDevelopmentChecklistItemStateEnum::NOT_APPLICABLE, 'na_reason' => 'Not needed for this family']));
    ProductDevelopmentHydrateChecklist::run($productDevelopment);

    $productDevelopment = MoveProductDevelopmentToNextStage::make()->action($productDevelopment->fresh(), $this->user);

    expect($productDevelopment->current_position)->toBe(2);

    $stages = $productDevelopment->stages()->get();
    expect($stages->firstWhere('stage', ProductDevelopmentStageEnum::BRIEF)->state)->toBe(ProductDevelopmentStageStateEnum::COMPLETED)
        ->and($stages->where('position', 2)->every(fn ($stage) => $stage->state === ProductDevelopmentStageStateEnum::IN_PROGRESS))->toBeTrue();

    $sentences = collect(GetProductDevelopmentActivity::run($productDevelopment))->pluck('sentence');
    expect($sentences)->toContain(__('Moved from :from to :to', ['from' => ProductDevelopmentStageEnum::positionLabel(1), 'to' => ProductDevelopmentStageEnum::positionLabel(2)]))
        ->and($sentences)->toContain(__('Created :code', ['code' => $productDevelopment->code]))
        ->and($sentences)->toContain(trans_choice('Added :count SKU|Added :count SKUs', 4, ['count' => 4]))
        ->and(collect(GetProductDevelopmentActivity::run($productDevelopment))->pluck('changes')->flatten(1)->pluck('label'))->not->toContain('Current Position');

    $gate = GetProductDevelopmentStageGate::run($productDevelopment);
    expect($gate['can_move'])->toBeFalse()
        ->and($gate['missing'])->toHaveKeys([
            ProductDevelopmentMemberRoleEnum::AGENT->value,
            ProductDevelopmentMemberRoleEnum::PURCHASING->value,
            ProductDevelopmentMemberRoleEnum::COMPLIANCE->value,
        ]);
})->depends('a buyer creates a development with 4 new SKUs and members');

test('filling a trade unit field completes its checklist item from the live value', function () {
    $productDevelopment = storeDevelopmentWithNewSkus($this, 1);
    $tradeUnit          = $productDevelopment->items->first()->tradeUnit;
    $tradeUnit->update(['tariff_code' => '34060000']);

    ProductDevelopmentHydrateChecklist::run($productDevelopment);

    $tariffItem = $productDevelopment->checklistItems()->where('key', 'trade_unit.tariff_code')->first();
    expect($tariffItem->state)->toBe(ProductDevelopmentChecklistItemStateEnum::COMPLETE);
});

test('a new SKU code that already exists is rejected', function () {
    $productDevelopment = storeDevelopmentWithNewSkus($this, 1);
    $existingCode       = $productDevelopment->items->first()->tradeUnit->code;

    expect(fn () => StoreProductDevelopment::make()->action($this->group, $this->user, [
        'name'  => 'Duplicate code',
        'type'  => 'redesign',
        'items' => [['code' => $existingCode, 'name' => 'Clash']],
    ]))->toThrow(ValidationException::class);
});

test('the list page shows developments with stage and missing count', function () {
    $productDevelopment = storeDevelopmentWithNewSkus($this, 2);

    get(route('grp.product_developments.index', ['filter' => ['global' => $productDevelopment->code]]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Goods/ProductDevelopments')
            ->has('pageHead')
            ->has('viewSwitch', 3)
            ->has('filterBar.counts.status')
            ->where('filterBar.statuses', fn ($statuses) => collect($statuses)->pluck('color', 'key')->all() === collect(ProductDevelopmentStatusEnum::cases())->mapWithKeys(fn ($status) => [$status->value => $status->color()])->all())
            ->where('filterBar.flags', fn ($flags) => collect($flags)->pluck('color', 'key')->all() === ['missing' => 'amber', 'overdue' => 'red'])
            ->has('filterBar.options.stage', ProductDevelopmentStageEnum::lastPosition())
            ->has('data.data', 1)
            ->where('data.data.0.code', $productDevelopment->code)
            ->where('data.data.0.stage_position', 1)
            ->where('data.data.0.number_required_missing', $productDevelopment->stats->fresh()->number_required_missing));
});

test('the board shows the same development in its stage column with the same missing count', function () {
    $productDevelopment = storeDevelopmentWithNewSkus($this, 2);
    $missing            = $productDevelopment->stats->fresh()->number_required_missing;

    get(route('grp.product_developments.board'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Goods/ProductDevelopmentsBoard')
            ->has('columns', 8)
            ->where('columns.0.position', 1)
            ->where('columns.0.cards', fn ($cards) => collect($cards)->contains(fn ($card) => $card['code'] === $productDevelopment->code && $card['number_required_missing'] === $missing))
            ->where('columns.0.cards', function ($cards) use ($productDevelopment, $missing) {
                $card = collect($cards)->firstWhere('code', $productDevelopment->code);

                return array_sum($card['missing_by_role']) === $missing
                    && $card['number_done'] === $card['number_required'] - $missing
                    && $card['owner_user']['id'] === $this->user->id
                    && $card['type_color'] === 'teal'
                    && $card['current_stage_due_at'] !== null;
            })
            ->has('roles.agent.icon'));
});

test('the development page shows the header, stage bar and a blocked move with its reason', function () {
    $productDevelopment = storeDevelopmentWithNewSkus($this, 2);

    get(route('grp.product_developments.show', ['productDevelopment' => $productDevelopment->slug]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Goods/ProductDevelopment')
            ->has('breadcrumbs', 3)
            ->has('header')
            ->has('overview.strip', count(ProductDevelopmentStageEnum::cases()) + 1)
            ->has('overview.roles', 5)
            ->has('overview.products', 2)
            ->where('overview.waiting_on', fn ($rows) => count($rows) > 0 && collect($rows)->every(fn ($row) => in_array($row['tab'], ['brief', 'products', 'compliance'], true)
                && \App\Models\Goods\ProductDevelopmentChecklistItem::whereKey((int) str_replace('pd-check-', '', $row['target']))->where('product_development_id', $productDevelopment->id)->exists()))
            ->where('header.type.color', 'teal')
            ->where('recentActivity', fn ($entries) => count($entries) > 0 && count($entries) <= 5)
            ->where('stageMove.can_move', false)
            ->where('stageMove.reason', fn ($reason) => str_contains($reason, __('Buyer')))
            ->where('tabs.current', 'brief')
            ->has('brief.members', 2)
            ->has('tabs.navigation', 7)
            ->where('tabs.navigation.products.number', 2));
});

test('the create page and the store endpoint create a development', function () {
    get(route('grp.product_developments.create'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Goods/CreateProductDevelopment')
            ->has('options.roles', count(ProductDevelopmentMemberRoleEnum::cases())));

    $suffix = Str::upper(Str::random(5));
    $before = ProductDevelopment::count();

    post(route('grp.models.product_development.store'), [
        'name'    => 'Rose soaps '.$suffix,
        'type'    => 'new',
        'members' => [['user_id' => $this->user->id, 'role' => 'buyer']],
        'items'   => [['code' => 'RS-'.$suffix, 'name' => 'Rose soap']],
    ])->assertRedirect();

    expect(ProductDevelopment::count())->toBe($before + 1);
});

test('the group menu shows Product development just above Trade Units', function () {
    $navigation = GetGroupNavigation::run($this->user);
    $keys       = array_keys($navigation);

    expect($keys)->toContain('product-developments')
        ->and(array_search('product-developments', $keys))->toBe(array_search('trade-units', $keys) - 1);
});

test('the filter bar counts match the list when a quick filter is applied', function () {
    storeDevelopmentWithNewSkus($this, 1);

    $response = get(route('grp.product_developments.index'));
    $counts   = $response->viewData('page')['props']['filterBar']['counts'];

    get(route('grp.product_developments.index', ['filter' => ['missing' => 'yes'], 'perPage' => 500]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('data.meta.total', $counts['missing'])
            ->where('data.data', fn ($rows) => collect($rows)->every(fn ($row) => $row['number_required_missing'] > 0)));

    get(route('grp.product_developments.board', ['filter' => ['status' => 'active']]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('columns', fn ($columns) => collect($columns)->sum(fn ($column) => count($column['cards'])) === min($counts['status']['active'], ShowProductDevelopmentsBoard::MAX_CARDS)));
});

test('the owner changes the target dates and the change shows in the activity feed', function () {
    $productDevelopment = storeDevelopmentWithNewSkus($this, 1);

    patch(route('grp.models.product_development.update', ['productDevelopment' => $productDevelopment->id]), [
        'target_landing_date' => '2026-11-02',
        'target_online_date'  => '2026-11-20',
    ])->assertRedirect();

    $productDevelopment->refresh();
    expect($productDevelopment->target_online_date->toDateString())->toBe('2026-11-20');

    $changes = collect(GetProductDevelopmentActivity::run($productDevelopment))->pluck('changes')->flatten(1);
    expect($changes->firstWhere('label', __('Target online'))['new'])->toBe('20 Nov 2026');

    patch(route('grp.models.product_development.update', ['productDevelopment' => $productDevelopment->id]), [
        'target_landing_date' => '2026-12-01',
        'target_online_date'  => '2026-11-20',
    ])->assertSessionHasErrors('target_online_date');
});

test('the owner adds and removes members and edits markets from the brief, and it shows in the activity feed', function () {
    $productDevelopment = StoreProductDevelopment::make()->action($this->group, $this->user, [
        'name'  => 'No members yet '.Str::upper(Str::random(5)),
        'type'  => 'new',
        'items' => [['code' => 'NM-'.Str::upper(Str::random(5)), 'name' => 'No members SKU']],
    ]);
    expect($productDevelopment->members()->count())->toBe(0);

    get(route('grp.product_developments.show', ['productDevelopment' => $productDevelopment->slug]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('editor.options.users')
            ->where('editor.values.members', []));

    patch(route('grp.models.product_development.members.sync', ['productDevelopment' => $productDevelopment->id]), [
        'members' => [['user_id' => $this->user->id, 'role' => ProductDevelopmentMemberRoleEnum::BUYER->value]],
    ])->assertRedirect();
    expect($productDevelopment->members()->count())->toBe(1);

    patch(route('grp.models.product_development.update', ['productDevelopment' => $productDevelopment->id]), [
        'markets' => ['uk', 'eu'],
    ])->assertRedirect();
    expect($productDevelopment->fresh()->markets)->toBe(['uk', 'eu']);

    patch(route('grp.models.product_development.members.sync', ['productDevelopment' => $productDevelopment->id]), [
        'members' => [],
    ])->assertRedirect();
    expect($productDevelopment->members()->count())->toBe(0);

    $sentences = collect(GetProductDevelopmentActivity::run($productDevelopment))->pluck('sentence');
    expect($sentences)->toContain(__('Added members'))
        ->and($sentences)->toContain(__('Removed members'));
});

test('a new SDS replaces the old one on the trade unit and keeps it as an earlier version', function () {
    $productDevelopment = storeDevelopmentWithNewSkus($this, 1);
    $item               = $productDevelopment->items->first();
    $tradeUnit          = $item->tradeUnit;

    UploadProductDevelopmentFiles::make()->action($productDevelopment, $this->user, [
        'files'                       => [UploadedFile::fake()->createWithContent('sds-v1.pdf', 'SDS version one '.Str::random(20))],
        'scope'                       => 'sds',
        'product_development_item_id' => $item->id,
    ]);
    $firstMediaId = $tradeUnit->attachments()->wherePivot('scope', 'sds')->value('media.id');
    expect($firstMediaId)->not->toBeNull();

    UploadProductDevelopmentFiles::make()->action($productDevelopment, $this->user, [
        'files'                       => [UploadedFile::fake()->createWithContent('sds-v2.pdf', 'SDS version two '.Str::random(20))],
        'scope'                       => 'sds',
        'product_development_item_id' => $item->id,
    ]);

    $onTradeUnit = $tradeUnit->attachments()->wherePivot('scope', 'sds')->pluck('media.id');
    expect($onTradeUnit)->toHaveCount(1)
        ->and($onTradeUnit->first())->not->toBe($firstMediaId);

    $files = $productDevelopment->files()->where('scope', 'sds')->orderBy('version')->get();
    expect($files)->toHaveCount(2)
        ->and($files[0]->version)->toBe(1)
        ->and($files[0]->superseded_at)->not->toBeNull()
        ->and($files[0]->media_id)->toBe($firstMediaId)
        ->and($files[1]->version)->toBe(2)
        ->and($files[1]->superseded_at)->toBeNull();

    $sentences = collect(GetProductDevelopmentActivity::run($productDevelopment))->pluck('sentence');
    expect($sentences->contains(fn ($sentence) => str_contains($sentence, 'SDS (v2)') && str_contains($sentence, $tradeUnit->code)))->toBeTrue();
});

test('an agent uploads six factory photos for one SKU and none of them replaces another', function () {
    $productDevelopment = storeDevelopmentWithNewSkus($this, 1);
    $item               = $productDevelopment->items->first();

    UploadProductDevelopmentFiles::make()->action($productDevelopment, $this->user, [
        'files'                       => collect(range(1, 6))->map(fn (int $index) => UploadedFile::fake()->createWithContent("photo-$index.jpg", "photo $index ".Str::random(20)))->all(),
        'scope'                       => 'factory_photo',
        'product_development_item_id' => $item->id,
    ]);

    $photos = $productDevelopment->files()->where('scope', 'factory_photo')->get();
    expect($photos)->toHaveCount(6)
        ->and($photos->whereNotNull('superseded_at'))->toHaveCount(0)
        ->and($photos->pluck('media_id')->unique())->toHaveCount(6);

    expect(collect(GetProductDevelopmentActivity::run($productDevelopment))->pluck('sentence')->first())->toContain('6');

    get(route('grp.product_developments.show', ['productDevelopment' => $productDevelopment->slug, 'tab' => 'files']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('tabs.navigation.files.number', 6)
            ->has('files.groups', 1)
            ->has('files.groups.0.current', 6)
            ->where('files.groups.0.sku', $item->tradeUnit->code)
            ->has('files.upload.scopes'));
});

test('a per-SKU file needs its SKU', function () {
    $productDevelopment = storeDevelopmentWithNewSkus($this, 1);

    expect(fn () => UploadProductDevelopmentFiles::make()->action($productDevelopment, $this->user, [
        'files' => [UploadedFile::fake()->createWithContent('sds.pdf', 'no sku '.Str::random(20))],
        'scope' => 'sds',
    ]))->toThrow(ValidationException::class);
});

function newGuestUser(object $test): \App\Models\SysAdmin\User
{
    return \App\Actions\SysAdmin\Guest\StoreGuest::make()->action($test->group, [...\App\Models\SysAdmin\Guest::factory()->definition(), 'positions' => []])->getUser();
}

function developmentTasks(ProductDevelopment $productDevelopment): \Illuminate\Support\Collection
{
    return \App\Models\Tasks\StaffTask::where('model_type', 'ProductDevelopment')->where('model_id', $productDevelopment->id)->get();
}

test('creating a development opens the agent and compliance tasks once, for the right people', function () {
    $agent      = newAgentUser($this);
    $compliance = newGuestUser($this);

    $productDevelopment = StoreProductDevelopment::make()->action($this->group, $this->user, [
        'name'     => 'Tasks check '.Str::upper(Str::random(5)),
        'agent_id' => testAgent($this)->id,
        'type'    => 'new',
        'members' => [
            ['user_id' => $agent->id, 'role' => ProductDevelopmentMemberRoleEnum::AGENT->value],
            ['user_id' => $compliance->id, 'role' => ProductDevelopmentMemberRoleEnum::COMPLIANCE->value],
        ],
        'items'   => [['code' => 'TK-'.Str::upper(Str::random(5)), 'name' => 'Tasks SKU']],
    ]);

    $tasks = developmentTasks($productDevelopment)->keyBy(fn ($task) => $task->data['product_development_event']);
    expect($tasks->keys()->sort()->values()->all())->toBe([CreateProductDevelopmentTask::SPECS, CreateProductDevelopmentTask::COMPLIANCE_SCOPE])
        ->and($tasks[CreateProductDevelopmentTask::SPECS]->assignee_id)->toBe($agent->id)
        ->and($tasks[CreateProductDevelopmentTask::SPECS]->due_at)->not->toBeNull()
        ->and($tasks[CreateProductDevelopmentTask::COMPLIANCE_SCOPE]->assignee_id)->toBe($compliance->id);

    expect(CreateProductDevelopmentTask::run($productDevelopment, CreateProductDevelopmentTask::SPECS))->toBeNull()
        ->and(developmentTasks($productDevelopment))->toHaveCount(2);
});

test('entering artwork to supplier asks purchasing to send it, and once done asks the agent to confirm', function () {
    $purchasing = newGuestUser($this);
    $agent      = newAgentUser($this);
    $productDevelopment = StoreProductDevelopment::make()->action($this->group, $this->user, [
        'name'     => 'Artwork check '.Str::upper(Str::random(5)),
        'agent_id' => testAgent($this)->id,
        'type'    => 'new',
        'members' => [
            ['user_id' => $purchasing->id, 'role' => ProductDevelopmentMemberRoleEnum::PURCHASING->value],
            ['user_id' => $agent->id, 'role' => ProductDevelopmentMemberRoleEnum::AGENT->value],
        ],
        'items'   => [['code' => 'AW-'.Str::upper(Str::random(5)), 'name' => 'Artwork SKU']],
    ]);
    $productDevelopment->checklistItems()->update(['state' => ProductDevelopmentChecklistItemStateEnum::NOT_APPLICABLE->value, 'na_reason' => 'test']);
    $productDevelopment->update([
        'current_position' => ProductDevelopmentStageEnum::FINAL_APPROVAL->position(),
        'data'             => ['sign_off' => ['buyer' => ['user_id' => $this->user->id, 'name' => 'Buyer', 'at' => now()->toIso8601String()], 'compliance' => ['user_id' => $this->user->id, 'name' => 'Compliance', 'at' => now()->toIso8601String()]], 'artwork_locked_at' => now()->toIso8601String()],
    ]);
    ProductDevelopmentHydrateChecklist::run($productDevelopment);

    MoveProductDevelopmentToNextStage::make()->action($productDevelopment->fresh(), $this->user);

    $sendTask = developmentTasks($productDevelopment)->first(fn ($task) => $task->data['product_development_event'] === CreateProductDevelopmentTask::SEND_ARTWORK);
    expect($sendTask)->not->toBeNull()
        ->and($sendTask->assignee_id)->toBe($purchasing->id);

    \App\Actions\Tasks\UpdateStaffTask::run($sendTask, $purchasing, ['status' => 'done']);

    $confirmTask = developmentTasks($productDevelopment)->first(fn ($task) => $task->data['product_development_event'] === CreateProductDevelopmentTask::CONFIRM_ARTWORK);
    expect($confirmTask)->not->toBeNull()
        ->and($confirmTask->assignee_id)->toBe($agent->id);
});

test('an SDS upload notifies compliance but not the uploader', function () {
    $compliance         = newGuestUser($this);
    $productDevelopment = StoreProductDevelopment::make()->action($this->group, $this->user, [
        'name'    => 'Notify check '.Str::upper(Str::random(5)),
        'type'    => 'new',
        'members' => [['user_id' => $compliance->id, 'role' => ProductDevelopmentMemberRoleEnum::COMPLIANCE->value]],
        'items'   => [['code' => 'NT-'.Str::upper(Str::random(5)), 'name' => 'Notify SKU']],
    ]);
    $complianceBefore = $compliance->notifications()->count();
    $uploaderBefore   = $this->user->notifications()->count();

    UploadProductDevelopmentFiles::make()->action($productDevelopment, $this->user, [
        'files'                       => [UploadedFile::fake()->createWithContent('sds.pdf', 'notify sds '.Str::random(20))],
        'scope'                       => 'sds',
        'product_development_item_id' => $productDevelopment->items->first()->id,
    ]);

    expect($compliance->notifications()->count())->toBe($complianceBefore + 1)
        ->and($this->user->notifications()->count())->toBe($uploaderBefore);
});

test('three days before a stage is due its role is reminded, and the owner gets a digest', function () {
    $agent              = newAgentUser($this);
    $productDevelopment = StoreProductDevelopment::make()->action($this->group, $this->user, [
        'name'     => 'Reminder check '.Str::upper(Str::random(5)),
        'agent_id' => testAgent($this)->id,
        'type'    => 'new',
        'members' => [['user_id' => $agent->id, 'role' => ProductDevelopmentMemberRoleEnum::AGENT->value]],
        'items'   => [['code' => 'RM-'.Str::upper(Str::random(5)), 'name' => 'Reminder SKU']],
    ]);
    $today = \Illuminate\Support\Carbon::today();
    $productDevelopment->stages()->where('stage', ProductDevelopmentStageEnum::PRODUCT_SPECS->value)->update(['state' => ProductDevelopmentStageStateEnum::IN_PROGRESS->value, 'due_at' => $today->copy()->addDays(3)->toDateString()]);

    $agentBefore = $agent->notifications()->count();
    $ownerBefore = $this->user->notifications()->count();

    SendProductDevelopmentReminders::run($today);

    expect($agent->notifications()->count())->toBe($agentBefore + 1)
        ->and($this->user->notifications()->count())->toBeGreaterThan($ownerBefore);
});

test('the owner changes a stage due date and it shows in the activity feed', function () {
    $productDevelopment = storeDevelopmentWithNewSkus($this, 1);
    $stage              = $productDevelopment->stages()->where('stage', ProductDevelopmentStageEnum::CONCEPT->value)->first();

    patch(route('grp.models.product_development.stage.due_date', ['productDevelopmentStage' => $stage->id]), ['due_at' => '2026-12-24'])->assertRedirect();

    expect($stage->fresh()->due_at->toDateString())->toBe('2026-12-24')
        ->and(collect(GetProductDevelopmentActivity::run($productDevelopment))->pluck('sentence'))->toContain(__('Changed the due date of :stage', ['stage' => __('Concept')]));
});

test('a role task waits for someone holding the role, then later holders join as collaborators', function () {
    $productDevelopment = storeDevelopmentWithNewSkus($this, 1);
    $specsTask          = fn () => developmentTasks($productDevelopment)->first(fn ($task) => $task->data['product_development_event'] === CreateProductDevelopmentTask::SPECS);

    expect($specsTask())->toBeNull();

    $firstAgent  = newAgentUser($this);
    $secondAgent = newAgentUser($this);
    $members     = $productDevelopment->members()->get(['user_id', 'role'])->map(fn ($member) => ['user_id' => $member->user_id, 'role' => $member->role->value])->all();

    SyncProductDevelopmentMembers::make()->action($productDevelopment, ['members' => [...$members, ['user_id' => $firstAgent->id, 'role' => ProductDevelopmentMemberRoleEnum::AGENT->value]]]);
    expect($specsTask()?->assignee_id)->toBe($firstAgent->id);

    SyncProductDevelopmentMembers::make()->action($productDevelopment, ['members' => [
        ...$members,
        ['user_id' => $firstAgent->id, 'role' => ProductDevelopmentMemberRoleEnum::AGENT->value],
        ['user_id' => $secondAgent->id, 'role' => ProductDevelopmentMemberRoleEnum::AGENT->value],
    ]]);

    $task = $specsTask();
    expect($task->assignee_id)->toBe($firstAgent->id)
        ->and($task->collaborators()->pluck('users.id')->all())->toContain($secondAgent->id)
        ->and(developmentTasks($productDevelopment)->filter(fn ($row) => $row->data['product_development_event'] === CreateProductDevelopmentTask::SPECS))->toHaveCount(1);
});

function developmentWithSupplier(object $test, array $members = []): ProductDevelopment
{
    $agent    = \App\Actions\SupplyChain\Agent\StoreAgent::make()->action($test->group, \App\Models\SupplyChain\Agent::factory()->definition());
    $supplier = \App\Actions\SupplyChain\Supplier\StoreSupplier::make()->action($agent, \App\Models\SupplyChain\Supplier::factory()->definition());

    return StoreProductDevelopment::make()->action($test->group, $test->user, [
        'name'        => 'Checklist check '.Str::upper(Str::random(5)),
        'type'        => 'new',
        'supplier_id' => $supplier->id,
        'members'     => $members,
        'items'       => [['code' => 'CL-'.Str::upper(Str::random(5)), 'name' => 'Checklist SKU']],
    ]);
}

test('saving a spec item writes the real trade unit field and records who saved it', function () {
    $agent              = newAgentUser($this);
    $productDevelopment = storeDevelopmentWithNewSkus($this, 1);
    \App\Actions\Goods\ProductDevelopment\SyncProductDevelopmentMembers::make()->action($productDevelopment, ['members' => [['user_id' => $agent->id, 'role' => 'agent']]]);
    $item         = $productDevelopment->items->first();
    $netWeight    = $productDevelopment->checklistItems()->where('product_development_item_id', $item->id)->where('key', 'trade_unit.net_weight')->first();
    $missingBefore = $productDevelopment->stats->fresh()->number_required_missing;

    actingAs($agent);
    patch(route('grp.models.product_development.checklist_item.update', ['productDevelopmentChecklistItem' => $netWeight->id]), ['operation' => 'save', 'value' => 250])->assertRedirect();

    expect($item->tradeUnit->fresh()->net_weight)->toBe(250)
        ->and($netWeight->fresh()->state)->toBe(ProductDevelopmentChecklistItemStateEnum::COMPLETE)
        ->and($netWeight->fresh()->completed_by_id)->toBe($agent->id)
        ->and($productDevelopment->stats->fresh()->number_required_missing)->toBe($missingBefore - 1);

    $compliance = $productDevelopment->checklistItems()->where('role', 'compliance')->first();
    patch(route('grp.models.product_development.checklist_item.update', ['productDevelopmentChecklistItem' => $compliance->id]), ['operation' => 'save', 'value' => '3406'])->assertForbidden();
});

test('the buyer answers and marks N/A with a reason, then the brief can move on', function () {
    $productDevelopment = storeDevelopmentWithNewSkus($this, 1);
    [$first, $second]   = $productDevelopment->checklistItems()->where('role', 'buyer')->orderBy('id')->get()->all();

    UpdateProductDevelopmentChecklistItem::make()->action($first, $this->user, ['operation' => 'save', 'value' => 'Women 25-45, £12-15']);
    expect(fn () => UpdateProductDevelopmentChecklistItem::make()->action($second, $this->user, ['operation' => 'not_applicable']))->toThrow(ValidationException::class);
    UpdateProductDevelopmentChecklistItem::make()->action($second, $this->user, ['operation' => 'not_applicable', 'na_reason' => 'Same mood board as the old range']);

    expect($first->fresh()->value)->toBe(['text' => 'Women 25-45, £12-15'])
        ->and($second->fresh()->state)->toBe(ProductDevelopmentChecklistItemStateEnum::NOT_APPLICABLE)
        ->and(GetProductDevelopmentStageGate::run($productDevelopment->fresh())['can_move'])->toBeTrue();
});

test('supplier product fields wait for a linked supplier product, and charges go to the supplier notes', function () {
    $productDevelopment = developmentWithSupplier($this);
    $item               = $productDevelopment->items->first();
    $unitsPerCarton     = $productDevelopment->checklistItems()->where('product_development_item_id', $item->id)->where('key', 'supplier_product.units_per_carton')->first();

    expect(fn () => UpdateProductDevelopmentChecklistItem::make()->action($unitsPerCarton, $this->user, ['operation' => 'save', 'value' => 24]))->toThrow(ValidationException::class);

    LinkProductDevelopmentSupplierProduct::make()->action($item, $this->user, ['code' => 'SP-'.Str::upper(Str::random(6)), 'cost' => 1.25, 'units_per_pack' => 1, 'units_per_carton' => 12]);
    $item = $item->fresh();
    expect($item->supplier_product_id)->not->toBeNull()
        ->and($item->supplierProduct->tradeUnits()->where('trade_units.id', $item->trade_unit_id)->exists())->toBeTrue();

    UpdateProductDevelopmentChecklistItem::make()->action($unitsPerCarton->fresh(), $this->user, ['operation' => 'save', 'value' => 24]);
    expect($unitsPerCarton->fresh()->state)->toBe(ProductDevelopmentChecklistItemStateEnum::COMPLETE)
        ->and($item->supplierProduct->fresh()->units_per_carton)->toBe(24);

    $charge      = $productDevelopment->checklistItems()->where('key', 'charge_label_print')->first();
    $notesBefore = \App\Models\Procurement\ProcurementNote::where('subject_type', 'Supplier')->where('subject_id', $productDevelopment->supplier_id)->count();
    UpdateProductDevelopmentChecklistItem::make()->action($charge, $this->user, ['operation' => 'save', 'value' => 'USD 0.05 per label, min 5,000']);

    $note = \App\Models\Procurement\ProcurementNote::where('subject_type', 'Supplier')->where('subject_id', $productDevelopment->supplier_id)->latest('id')->first();
    expect(\App\Models\Procurement\ProcurementNote::where('subject_type', 'Supplier')->where('subject_id', $productDevelopment->supplier_id)->count())->toBe($notesBefore + 1)
        ->and($note->body)->toContain('USD 0.05 per label');
});

test('a supplier fixes the agent of a development', function () {
    $productDevelopment = developmentWithSupplier($this);
    $otherAgent         = \App\Actions\SupplyChain\Agent\StoreAgent::make()->action($this->group, \App\Models\SupplyChain\Agent::factory()->definition());

    expect($productDevelopment->agent_id)->toBe($productDevelopment->supplier->agent_id)
        ->and(fn () => StoreProductDevelopment::make()->action($this->group, $this->user, [
            'name'        => 'Mismatched agent',
            'type'        => 'new',
            'supplier_id' => $productDevelopment->supplier_id,
            'agent_id'    => $otherAgent->id,
            'items'       => [['code' => 'MA-'.Str::upper(Str::random(5)), 'name' => 'Mismatch SKU']],
        ]))->toThrow(ValidationException::class);
});

test('a design round runs from v1 through pinned comments and changes to an approved, locked v2 that opens the label task', function () {
    $designer           = newGuestUser($this);
    $buyer              = newGuestUser($this);
    $agent              = newAgentUser($this);
    $productDevelopment = storeDevelopmentWithNewSkus($this, 1);
    SyncProductDevelopmentMembers::make()->action($productDevelopment, ['members' => [
        ['user_id' => $designer->id, 'role' => 'designer'],
        ['user_id' => $buyer->id, 'role' => 'buyer'],
        ['user_id' => $agent->id, 'role' => 'agent'],
    ]]);

    expect(fn () => StoreProductDevelopmentDesignVersion::make()->action($productDevelopment, $designer, ['round' => 'packaging']))->toThrow(ValidationException::class);
    approveDesign($productDevelopment, 'concept', ['designer' => $designer, 'buyer' => $buyer, 'agent' => $agent]);

    $v1 = StoreProductDevelopmentDesignVersion::make()->action($productDevelopment, $designer, ['round' => 'packaging']);
    expect($v1->version)->toBe(1)
        ->and(fn () => SubmitProductDevelopmentDesignVersion::make()->action($v1, $designer))->toThrow(ValidationException::class);

    $layout                              = $v1->data['layout'];
    $layout['width']                     = 60;
    $layout['length']                    = 40;
    $layout['height']                    = 120;
    $layout['faces']['front']['elements'] = [['id' => 'name', 'type' => 'text', 'x' => 5, 'y' => 40, 'w' => 50, 'h' => 12, 'text' => 'Incense Cones', 'font_size' => 14]];
    $layout['faces']['back']['elements']  = [['id' => 'warnings', 'type' => 'zone', 'zone' => 'compliance', 'x' => 4, 'y' => 30, 'w' => 52, 'h' => 30]];
    UpdateProductDevelopmentDesignVersion::make()->action($v1, $designer, ['layout' => $layout, 'notes' => 'First take']);
    SubmitProductDevelopmentDesignVersion::make()->action($v1->fresh(), $designer);

    $pin = StoreProductDevelopmentComment::make()->action($productDevelopment, $buyer, ['body' => 'Make the scent name larger', 'design_round_id' => $v1->id, 'pin' => ['x' => 0.42, 'y' => 0.5]]);
    expect($pin->model_type)->toBe('ProductDevelopmentDesignRound')
        ->and($pin->data['pin']['number'])->toBe(1);

    ReviewProductDevelopmentDesignVersion::make()->action($v1->fresh(), $buyer, ['operation' => 'request_changes']);
    expect($v1->fresh()->status)->toBe(ProductDevelopmentDesignRoundStatusEnum::CHANGES_REQUESTED);

    $v2 = StoreProductDevelopmentDesignVersion::make()->action($productDevelopment, $designer, ['round' => 'packaging']);
    expect($v2->version)->toBe(2)
        ->and($v2->data['layout']['faces']['front']['elements'][0]['text'])->toBe('Incense Cones');

    \App\Actions\Goods\ProductDevelopment\UpdateProductDevelopmentCommentStatus::make()->action($pin, $designer, ['status' => 'resolved']);
    expect($pin->fresh()->data['resolved_in_version'])->toBe(2);

    SubmitProductDevelopmentDesignVersion::make()->action($v2->fresh(), $designer);
    expect(fn () => ReviewProductDevelopmentDesignVersion::make()->action($v2->fresh(), $buyer, ['operation' => 'approve']))->toThrow(ValidationException::class)
        ->and(fn () => ReviewProductDevelopmentDesignVersion::make()->action($v2->fresh(), $buyer, ['operation' => 'check', 'check' => 'print_limits']))->toThrow(ValidationException::class);

    ReviewProductDevelopmentDesignVersion::make()->action($v2->fresh(), $agent, ['operation' => 'check', 'check' => 'print_limits']);
    ReviewProductDevelopmentDesignVersion::make()->action($v2->fresh(), $buyer, ['operation' => 'check', 'check' => 'matches_brief']);
    ReviewProductDevelopmentDesignVersion::make()->action($v2->fresh(), $buyer, ['operation' => 'check', 'check' => 'compliance_space']);
    ReviewProductDevelopmentDesignVersion::make()->action($v2->fresh(), $buyer, ['operation' => 'approve']);

    $v2 = $v2->fresh();
    expect($v2->status)->toBe(ProductDevelopmentDesignRoundStatusEnum::APPROVED)
        ->and($v2->locked_at)->not->toBeNull()
        ->and($v2->approved_by_id)->toBe($buyer->id)
        ->and(fn () => UpdateProductDevelopmentDesignVersion::make()->action($v2, $designer, ['notes' => 'too late']))->toThrow(ValidationException::class);

    $labelTask = developmentTasks($productDevelopment)->first(fn ($task) => ($task->data['product_development_event'] ?? null) === CreateProductDevelopmentTask::DESIGN_LABEL);
    $label     = StoreProductDevelopmentDesignVersion::make()->action($productDevelopment, $designer, ['round' => 'label', 'product_development_item_id' => $productDevelopment->items->first()->id]);
    expect($labelTask)->not->toBeNull()
        ->and($labelTask->assignee_id)->toBe($designer->id)
        ->and($label->version)->toBe(1)
        ->and($label->data['layout']['kind'])->toBe('sheet')
        ->and($label->data['layout']['width'])->toBeNull();

    actingAs($buyer);
    get(route('grp.product_developments.design', ['productDevelopment' => $productDevelopment->slug, 'round' => 'packaging']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Goods/ProductDevelopmentDesign')
            ->where('version.version', 2)
            ->where('version.status.value', 'approved')
            ->has('versions', 2)
            ->has('comments.threads', 0)
            ->where('previous.version', 1));

    get(route('grp.product_developments.design', ['productDevelopment' => $productDevelopment->slug, 'round' => 'packaging', 'version' => 1]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->where('version.version', 1)->has('comments.threads', 1));

    actingAs(newGuestUser($this));
    get(route('grp.product_developments.design', ['productDevelopment' => $productDevelopment->slug]))->assertForbidden();
});

test('the specs task follows the agent checklist: in progress, done, and reopened when an item goes missing again', function () {
    $agent              = newAgentUser($this);
    $productDevelopment = storeDevelopmentWithNewSkus($this, 1);
    SyncProductDevelopmentMembers::make()->action($productDevelopment, ['members' => [['user_id' => $agent->id, 'role' => 'agent']]]);

    $specsTask = fn () => developmentTasks($productDevelopment)->first(fn ($task) => ($task->data['product_development_event'] ?? null) === CreateProductDevelopmentTask::SPECS);
    expect($specsTask()->status)->toBe(\App\Enums\Tasks\StaffTaskStatusEnum::TODO);

    $agentItems = $productDevelopment->checklistItems()->where('role', 'agent')->where('required', true)->orderBy('id')->get();
    UpdateProductDevelopmentChecklistItem::make()->action($agentItems->first(), $agent, ['operation' => 'not_applicable', 'na_reason' => 'Test']);
    expect($specsTask()->status)->toBe(\App\Enums\Tasks\StaffTaskStatusEnum::IN_PROGRESS);

    $agentItems->skip(1)->each(fn ($checklistItem) => UpdateProductDevelopmentChecklistItem::make()->action($checklistItem->fresh(), $agent, ['operation' => 'not_applicable', 'na_reason' => 'Test']));
    expect($specsTask()->status)->toBe(\App\Enums\Tasks\StaffTaskStatusEnum::DONE)
        ->and($specsTask()->data['closed_by_development'])->toBeTrue();

    UpdateProductDevelopmentChecklistItem::make()->action($agentItems->first()->fresh(), $agent, ['operation' => 'reopen']);
    expect($specsTask()->status)->toBe(\App\Enums\Tasks\StaffTaskStatusEnum::IN_PROGRESS);
});

test('the agent role only goes to someone working for the development\'s agent', function () {
    $productDevelopment = storeDevelopmentWithNewSkus($this, 1);
    $outsider           = newGuestUser($this);

    expect(fn () => SyncProductDevelopmentMembers::make()->action($productDevelopment, ['members' => [['user_id' => $outsider->id, 'role' => 'agent']]]))->toThrow(ValidationException::class);

    SyncProductDevelopmentMembers::make()->action($productDevelopment, ['members' => [['user_id' => $outsider->id, 'role' => 'designer']]]);
    expect($productDevelopment->members()->where('user_id', $outsider->id)->value('role')->value)->toBe('designer');

    $agentStaff = newAgentUser($this);
    SyncProductDevelopmentMembers::make()->action($productDevelopment, ['members' => [['user_id' => $agentStaff->id, 'role' => 'agent']]]);
    expect($productDevelopment->members()->where('user_id', $agentStaff->id)->exists())->toBeTrue();
});

test('a new SKU code is checked before saving, with a readable message', function () {
    $taken = storeDevelopmentWithNewSkus($this, 1)->items->first()->tradeUnit;

    get(route('grp.product_developments.json.sku_code', ['code' => strtolower($taken->code)]))
        ->assertOk()
        ->assertJson(['valid' => false])
        ->assertJsonPath('message', __('Already used by trade unit :code · :name', ['code' => $taken->code, 'name' => $taken->name]));

    get(route('grp.product_developments.json.sku_code', ['code' => 'FREE-'.Str::upper(Str::random(8))]))->assertOk()->assertJson(['valid' => true]);

    post(route('grp.models.product_development.store'), [
        'name'  => 'Duplicate code',
        'type'  => 'new',
        'items' => [['code' => $taken->code, 'name' => 'Clash']],
    ])->assertSessionHasErrors(['items.0.code' => __('This code is already used by another trade unit')]);
});

function approveDesign(ProductDevelopment $productDevelopment, string $round, array $people, ?int $itemId = null): \App\Models\Goods\ProductDevelopmentDesignRound
{
    $version = StoreProductDevelopmentDesignVersion::make()->action($productDevelopment, $people['designer'], ['round' => $round, 'product_development_item_id' => $itemId]);
    if ($version->data['source'] === 'upload') {
        \App\Actions\Goods\ProductDevelopment\Design\UploadProductDevelopmentDesignArtwork::make()->action($version, $people['designer'], ['file' => UploadedFile::fake()->create('artwork.pdf', 120, 'application/pdf')]);
    } else {
        $layout = $version->data['layout'];
        $layout['width'] ??= 60;
        $layout['length'] ??= 40;
        $layout['height'] ??= 120;
        UpdateProductDevelopmentDesignVersion::make()->action($version, $people['designer'], ['layout' => $layout]);
    }
    SubmitProductDevelopmentDesignVersion::make()->action($version->fresh(), $people['designer']);
    ReviewProductDevelopmentDesignVersion::make()->action($version->fresh(), $people['agent'], ['operation' => 'check', 'check' => 'print_limits']);
    ReviewProductDevelopmentDesignVersion::make()->action($version->fresh(), $people['buyer'], ['operation' => 'check', 'check' => 'matches_brief']);
    ReviewProductDevelopmentDesignVersion::make()->action($version->fresh(), $people['buyer'], ['operation' => 'check', 'check' => 'compliance_space']);
    ReviewProductDevelopmentDesignVersion::make()->action($version->fresh(), $people['buyer'], ['operation' => 'approve']);

    return $version->fresh();
}

function developmentWithDesignTeam(object $test, int $numberSkus): array
{
    $people             = ['designer' => newGuestUser($test), 'buyer' => newGuestUser($test), 'agent' => newAgentUser($test), 'compliance' => newGuestUser($test)];
    $productDevelopment = storeDevelopmentWithNewSkus($test, $numberSkus);
    SyncProductDevelopmentMembers::make()->action($productDevelopment, ['members' => collect($people)->map(fn ($user, $role) => ['user_id' => $user->id, 'role' => $role])->values()->all()]);

    return [$productDevelopment->fresh(), $people];
}

test('labels are designed and approved per SKU, and the label round only counts as approved once every SKU is', function () {
    [$productDevelopment, $people] = developmentWithDesignTeam($this, 2);
    [$first, $second]              = $productDevelopment->items()->orderBy('id')->get()->all();

    approveDesign($productDevelopment, 'concept', $people);
    $packaging = approveDesign($productDevelopment, 'packaging', $people);

    expect(fn () => StoreProductDevelopmentDesignVersion::make()->action($productDevelopment, $people['designer'], ['round' => 'label']))->toThrow(ValidationException::class);

    $firstLabel = approveDesign($productDevelopment, 'label', $people, $first->id);
    expect($firstLabel->product_development_item_id)->toBe($first->id)
        ->and($firstLabel->data['layout']['kind'])->toBe('sheet')
        ->and(\App\Actions\Goods\ProductDevelopment\Design\ProductDevelopmentDesignRounds::isApproved($productDevelopment, \App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundEnum::LABEL))->toBeFalse();

    $labelTask = fn () => developmentTasks($productDevelopment)->first(fn ($task) => ($task->data['product_development_event'] ?? null) === CreateProductDevelopmentTask::DESIGN_LABEL);
    expect($labelTask()->status->isOpen())->toBeTrue();

    $secondLabel = approveDesign($productDevelopment, 'label', $people, $second->id);
    expect($secondLabel->version)->toBe(1)
        ->and(\App\Actions\Goods\ProductDevelopment\Design\ProductDevelopmentDesignRounds::isApproved($productDevelopment, \App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundEnum::LABEL))->toBeTrue()
        ->and($labelTask()->status)->toBe(\App\Enums\Tasks\StaffTaskStatusEnum::DONE);

    actingAs($people['buyer']);
    get(route('grp.product_developments.design', ['productDevelopment' => $productDevelopment->slug, 'round' => 'label', 'sku' => $second->id]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('sku', $second->id)
            ->has('skus', 2)
            ->where('version.id', $secondLabel->id)
            ->has('versions', 1));
});

test('final approval needs the buyer and compliance to sign off, which locks the artwork', function () {
    [$productDevelopment, $people] = developmentWithDesignTeam($this, 1);
    $productDevelopment->updateQuietly(['current_position' => ProductDevelopmentStageEnum::FINAL_APPROVAL->position()]);
    CreateProductDevelopmentTask::make()->ensureForDevelopment($productDevelopment->fresh(), $this->user);
    $productDevelopment = $productDevelopment->fresh();

    $signOffTask = fn (string $key) => developmentTasks($productDevelopment)->first(fn ($task) => ($task->data['product_development_event'] ?? null) === $key);
    expect($signOffTask(CreateProductDevelopmentTask::SIGN_OFF_BUYER)->assignee_id)->toBe($people['buyer']->id)
        ->and($signOffTask(CreateProductDevelopmentTask::SIGN_OFF_COMPLIANCE)->assignee_id)->toBe($people['compliance']->id)
        ->and(GetProductDevelopmentStageGate::run($productDevelopment)['can_move'])->toBeFalse();

    expect(fn () => \App\Actions\Goods\ProductDevelopment\SignOffProductDevelopment::make()->action($productDevelopment, $people['designer'], ['role' => 'buyer', 'operation' => 'sign']))->toThrow(ValidationException::class);

    \App\Actions\Goods\ProductDevelopment\SignOffProductDevelopment::make()->action($productDevelopment, $people['buyer'], ['role' => 'buyer', 'operation' => 'sign']);
    expect(GetProductDevelopmentStageGate::run($productDevelopment->fresh())['reason'])->toContain('Compliance')
        ->and($signOffTask(CreateProductDevelopmentTask::SIGN_OFF_BUYER)->status)->toBe(\App\Enums\Tasks\StaffTaskStatusEnum::DONE);

    \App\Actions\Goods\ProductDevelopment\SignOffProductDevelopment::make()->action($productDevelopment->fresh(), $people['compliance'], ['role' => 'compliance', 'operation' => 'sign']);
    $productDevelopment = $productDevelopment->fresh();
    expect($productDevelopment->data['artwork_locked_at'])->not->toBeNull()
        ->and((string) GetProductDevelopmentStageGate::run($productDevelopment)['reason'])->not->toContain('sign-off')
        ->and(fn () => StoreProductDevelopmentDesignVersion::make()->action($productDevelopment, $people['designer'], ['round' => 'concept']))->toThrow(ValidationException::class);

    \App\Actions\Goods\ProductDevelopment\SignOffProductDevelopment::make()->action($productDevelopment, $people['compliance'], ['role' => 'compliance', 'operation' => 'withdraw']);
    expect($productDevelopment->fresh()->data['artwork_locked_at'])->toBeNull()
        ->and($signOffTask(CreateProductDevelopmentTask::SIGN_OFF_COMPLIANCE)->status)->toBe(\App\Enums\Tasks\StaffTaskStatusEnum::IN_PROGRESS);
});

test('compliance confirms pictograms and label marks, and links ingredients, straight on the trade unit', function () {
    [$productDevelopment, $people] = developmentWithDesignTeam($this, 1);
    $item                          = $productDevelopment->items->first();
    $checklistItem                 = fn (string $key) => $productDevelopment->checklistItems()->where('product_development_item_id', $item->id)->where('key', $key)->first();

    expect($checklistItem('trade_unit.pictograms')->state)->toBe(ProductDevelopmentChecklistItemStateEnum::MISSING);

    UpdateProductDevelopmentChecklistItem::make()->action($checklistItem('trade_unit.pictograms'), $people['compliance'], ['operation' => 'save', 'value' => ['pictogram_flammable' => true, 'pictogram_toxic' => false]]);
    expect($item->tradeUnit->fresh()->pictogram_flammable)->toBeTrue()
        ->and($checklistItem('trade_unit.pictograms')->state)->toBe(ProductDevelopmentChecklistItemStateEnum::COMPLETE);

    UpdateProductDevelopmentChecklistItem::make()->action($checklistItem('trade_unit.label_info'), $people['compliance'], ['operation' => 'save', 'value' => ['markets' => ['uk'], 'ce_marking' => true, 'best_before' => null, 'label_info_approved' => true]]);
    $labelInfo = $item->tradeUnit->fresh()->label_info;
    expect($labelInfo['markets'])->toBe(['uk'])
        ->and($labelInfo['ce_marking'])->toBeTrue()
        ->and($labelInfo['label_info_approved'] ?? false)->toBeFalse()
        ->and($checklistItem('trade_unit.label_info')->state)->toBe(ProductDevelopmentChecklistItemStateEnum::COMPLETE);

    $ingredient = \App\Actions\Goods\Ingredient\StoreIngredient::make()->action($this->group, \App\Models\Goods\Ingredient::factory()->definition());
    UpdateProductDevelopmentChecklistItem::make()->action($checklistItem('trade_unit.ingredients'), $people['compliance'], ['operation' => 'save', 'value' => [$ingredient->slug]]);
    expect($item->tradeUnit->ingredients()->pluck('slug')->all())->toBe([$ingredient->slug])
        ->and($checklistItem('trade_unit.ingredients')->state)->toBe(ProductDevelopmentChecklistItemStateEnum::COMPLETE);

    ProductDevelopmentHydrateChecklist::run($productDevelopment->fresh());
    expect($checklistItem('trade_unit.pictograms')->state)->toBe(ProductDevelopmentChecklistItemStateEnum::COMPLETE);
});

test('send reminders tells each role what is waiting on it, and only the owner or purchasing can send them', function () {
    [$productDevelopment, $people] = developmentWithDesignTeam($this, 1);
    $productDevelopment->update(['current_position' => 2]);

    $reminded = \App\Actions\Goods\ProductDevelopment\RemindProductDevelopmentMembers::make()->action($productDevelopment, $this->user);
    $agentRows = \App\Actions\Goods\ProductDevelopment\RemindProductDevelopmentMembers::remindable($productDevelopment->fresh())[ProductDevelopmentMemberRoleEnum::AGENT->value];
    expect(\App\Actions\Goods\ProductDevelopment\RemindProductDevelopmentMembers::openAt($agentRows[0]))
        ->toContain('tab=products')
        ->toContain('focus=pd-check-'.$agentRows[0]['items'][0]);
    expect($reminded)->toContain('Agent')
        ->and($productDevelopment->fresh()->data['reminders_sent']['roles'])->toContain('Agent')
        ->and(collect(GetProductDevelopmentActivity::run($productDevelopment->fresh()))->pluck('sentence')->first())->toContain('Sent reminders to');

    actingAs($people['agent']);
    post(route('grp.models.product_development.remind', ['productDevelopment' => $productDevelopment->id]))->assertForbidden();
});

test('waiting on me lists what the person has to do across developments, and the dashboard widget only shows for members', function () {
    [$productDevelopment, $people] = developmentWithDesignTeam($this, 1);
    $waitingOn                     = new \App\Actions\Goods\ProductDevelopment\ProductDevelopmentWaitingOn();

    $agentRows = collect($waitingOn->forUser($people['agent']))->where('development.code', $productDevelopment->code);
    expect($agentRows)->not->toBeEmpty()
        ->and($agentRows->pluck('kind')->unique()->all())->toBe(['checklist'])
        ->and(\App\Actions\Goods\ProductDevelopment\ProductDevelopmentWaitingOn::dashboardWidget($people['agent']))->toBeInstanceOf(\Inertia\DeferProp::class)
        ->and(\App\Actions\Goods\ProductDevelopment\ProductDevelopmentWaitingOn::dashboardWidget(newGuestUser($this)))->toBeNull();

    actingAs($people['agent']);
    get(route('grp.product_developments.waiting_on_me'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Goods/ProductDevelopmentsWaitingOnMe')
            ->where('rows', fn ($rows) => collect($rows)->contains(fn ($row) => $row['development']['code'] === $productDevelopment->code
                && $row['kind'] === 'checklist'
                && count($row['fields']) > 0
                && $row['fields'][0]['can_edit'] === true)));
});

test('the label round shows the SKU pack text from compliance and its three barcodes', function () {
    [$productDevelopment, $people] = developmentWithDesignTeam($this, 1);
    $item                          = $productDevelopment->items->first();
    approveDesign($productDevelopment, 'concept', $people);
    approveDesign($productDevelopment, 'packaging', $people);
    \App\Actions\Goods\TradeUnit\UpdateTradeUnit::make()->action($item->tradeUnit, ['gpsr_warnings' => 'Keep away from flames', 'pictogram_flammable' => true]);

    actingAs($people['buyer']);
    get(route('grp.product_developments.design', ['productDevelopment' => $productDevelopment->slug, 'round' => 'label', 'sku' => $item->id]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('labelContent.sku.code', $item->tradeUnit->code)
            ->has('labelContent.barcodes', 3)
            ->where('labelContent.approved', false)
            ->has('labelContent.pictograms', 1)
            ->where('labelContent.text', fn ($lines) => collect($lines)->contains(fn ($line) => $line['value'] === 'Keep away from flames'))
            ->where('labelContent.values.text.warnings', 'Keep away from flames')
            ->where('labelContent.values.pictograms.pictogram_flammable', true)
            ->has('dataSources.text')
            ->has('dataSources.barcodes', 3));

    $label  = StoreProductDevelopmentDesignVersion::make()->action($productDevelopment, $people['designer'], ['round' => 'label', 'product_development_item_id' => $item->id, 'source' => 'editor']);
    $layout = [...$label->data['layout'], 'width' => 100, 'height' => 40];
    $layout['faces']['sheet']['elements'] = [
        ['id' => 'w', 'type' => 'data', 'source' => 'warnings', 'x' => 2, 'y' => 2, 'w' => 60, 'h' => 10],
        ['id' => 'p', 'type' => 'pictogram', 'source' => 'pictogram_flammable', 'x' => 70, 'y' => 2, 'w' => 10, 'h' => 10],
        ['id' => 'b', 'type' => 'barcode', 'source' => 'unit', 'x' => 60, 'y' => 20, 'w' => 37, 'h' => 18, 'group_id' => 'g1', 'locked' => true],
    ];
    $layout['faces']['sheet']['elements'][] = ['id' => 's', 'type' => 'shape', 'shape' => 'rounded', 'x' => 1, 'y' => 1, 'w' => 20, 'h' => 10, 'fill' => 'linear:45:#ff0000:#0000ff', 'stroke' => '#111827', 'stroke_px' => 2, 'radius' => 3];
    $layout['faces']['sheet']['background']['color'] = 'radial:#ffffff:#cccccc';
    $layout['faces']['sheet']['groups'] = ['g1' => 'Barcode block', 'gone' => 'No members'];
    UpdateProductDevelopmentDesignVersion::make()->action($label, $people['designer'], ['layout' => $layout]);
    expect($label->fresh()->data['layout']['faces']['sheet']['groups'])->toBe(['g1' => 'Barcode block']);
    $saved = collect($label->fresh()->data['layout']['faces']['sheet']['elements']);
    expect($saved->pluck('source')->filter()->values()->all())->toBe(['warnings', 'pictogram_flammable', 'unit'])
        ->and($saved->firstWhere('id', 's')['fill'])->toBe('linear:45:#ff0000:#0000ff')
        ->and($label->fresh()->data['layout']['faces']['sheet']['background']['color'])->toBe('radial:#ffffff:#cccccc')
        ->and($saved->firstWhere('id', 'b')['group_id'])->toBe('g1')
        ->and($saved->firstWhere('id', 'b')['locked'])->toBeTrue();
});

test('the agent completes factory photos angle by angle, and a new photo for an angle keeps the old one as an earlier version', function () {
    [$productDevelopment, $people] = developmentWithDesignTeam($this, 1);
    $item                          = $productDevelopment->items->first();
    $photos                        = $productDevelopment->checklistItems()->where('product_development_item_id', $item->id)->where('key', 'factory_photos')->first();
    $upload                        = \App\Actions\Goods\ProductDevelopment\UploadProductDevelopmentFactoryPhoto::make();

    expect($photos)->not->toBeNull()
        ->and(fn () => UpdateProductDevelopmentChecklistItem::make()->action($photos, $people['agent'], ['operation' => 'save', 'value' => 'done']))->toThrow(ValidationException::class);

    foreach (array_slice(\App\Actions\Goods\ProductDevelopment\UploadProductDevelopmentFactoryPhoto::ANGLES, 0, 5) as $angle) {
        $upload->action($photos->fresh(), $people['agent'], ['angle' => $angle, 'photo' => UploadedFile::fake()->image($angle.'.jpg')]);
    }
    expect($photos->fresh()->state)->toBe(ProductDevelopmentChecklistItemStateEnum::MISSING);

    $upload->action($photos->fresh(), $people['agent'], ['angle' => 'top', 'photo' => UploadedFile::fake()->image('top.jpg')]);
    expect($photos->fresh()->state)->toBe(ProductDevelopmentChecklistItemStateEnum::COMPLETE)
        ->and($photos->fresh()->completed_by_id)->toBe($people['agent']->id);

    $firstFront = $photos->fresh()->value['angles']['front'];
    $upload->action($photos->fresh(), $people['agent'], ['angle' => 'front', 'photo' => UploadedFile::fake()->image('front-2.jpg')]);
    expect($photos->fresh()->value['angles']['front'])->not->toBe($firstFront)
        ->and($productDevelopment->files()->find($firstFront)->superseded_at)->not->toBeNull()
        ->and($productDevelopment->files()->where('checklist_item_id', $photos->id)->whereNull('superseded_at')->count())->toBe(6);

    $editor = \App\Actions\Goods\ProductDevelopment\UI\GetProductDevelopmentChecklistEditor::run($productDevelopment->fresh(), $this->user, true);
    $front  = collect(collect(collect($editor['skus'])->firstWhere('id', $item->id)['rows'])->firstWhere('id', $photos->id)['value']['angles'])->firstWhere('key', 'front');
    expect($front['name'])->toBe('front.jpg')
        ->and($front['mime'])->toBe('image/jpeg')
        ->and($front['url'])->not->toBeNull();
});

test('concept is uploaded and marked up with pins and freehand lines, and packaging can be skipped with the label opening straight away', function () {
    [$productDevelopment, $people] = developmentWithDesignTeam($this, 1);

    expect(fn () => StoreProductDevelopmentDesignVersion::make()->action($productDevelopment, $people['designer'], ['round' => 'concept', 'source' => 'editor']))->toThrow(ValidationException::class);

    $concept = StoreProductDevelopmentDesignVersion::make()->action($productDevelopment, $people['designer'], ['round' => 'concept']);
    expect($concept->data['source'])->toBe('upload')
        ->and(fn () => SubmitProductDevelopmentDesignVersion::make()->action($concept, $people['designer']))->toThrow(ValidationException::class);

    \App\Actions\Goods\ProductDevelopment\Design\UploadProductDevelopmentDesignArtwork::make()->action($concept, $people['designer'], ['file' => UploadedFile::fake()->create('concept.pdf', 200, 'application/pdf')]);
    expect($concept->fresh()->data['upload']['kind'])->toBe('pdf');
    SubmitProductDevelopmentDesignVersion::make()->action($concept->fresh(), $people['designer']);

    $pin    = StoreProductDevelopmentComment::make()->action($productDevelopment, $people['buyer'], ['body' => 'Logo bigger', 'design_round_id' => $concept->id, 'pin' => ['x' => 0.3, 'y' => 0.2, 'page' => 2]]);
    $stroke = StoreProductDevelopmentComment::make()->action($productDevelopment, $people['buyer'], ['body' => 'This whole area', 'design_round_id' => $concept->id, 'stroke' => ['page' => 1, 'points' => [[0.1, 0.1], [0.2, 0.15], [0.3, 0.1]]]]);
    expect($pin->data['pin']['page'])->toBe(2)
        ->and($stroke->data['stroke']['number'])->toBe(2)
        ->and($stroke->data['stroke']['points'])->toHaveCount(3);

    foreach (['print_limits' => 'agent', 'matches_brief' => 'buyer', 'compliance_space' => 'buyer'] as $check => $person) {
        ReviewProductDevelopmentDesignVersion::make()->action($concept->fresh(), $people[$person], ['operation' => 'check', 'check' => $check]);
    }
    ReviewProductDevelopmentDesignVersion::make()->action($concept->fresh(), $people['buyer'], ['operation' => 'approve']);

    $skip = \App\Actions\Goods\ProductDevelopment\Design\SkipProductDevelopmentDesignRound::make();
    expect(fn () => $skip->action($productDevelopment->fresh(), $this->user, ['round' => 'concept', 'operation' => 'skip']))->toThrow(ValidationException::class);

    $skip->action($productDevelopment->fresh(), $this->user, ['round' => 'packaging', 'operation' => 'skip']);
    $productDevelopment = $productDevelopment->fresh();
    $tasks              = developmentTasks($productDevelopment);
    expect(\App\Actions\Goods\ProductDevelopment\Design\ProductDevelopmentDesignRounds::isUnlocked($productDevelopment, \App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundEnum::LABEL))->toBeTrue()
        ->and($tasks->first(fn ($task) => ($task->data['product_development_event'] ?? null) === CreateProductDevelopmentTask::DESIGN_PACKAGING)?->status->isOpen() ?? false)->toBeFalse()
        ->and($tasks->first(fn ($task) => ($task->data['product_development_event'] ?? null) === CreateProductDevelopmentTask::DESIGN_LABEL))->not->toBeNull();

    $productDevelopment->updateQuietly(['current_position' => ProductDevelopmentStageEnum::PACKAGING->position()]);
    expect(GetProductDevelopmentStageGate::run($productDevelopment->fresh())['reason'] ?? '')->not->toContain('packaging');

    $skip->action($productDevelopment->fresh(), $this->user, ['round' => 'packaging', 'operation' => 'undo']);
    expect(\App\Actions\Goods\ProductDevelopment\Design\ProductDevelopmentDesignRounds::isSkipped($productDevelopment->fresh(), \App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundEnum::PACKAGING))->toBeFalse();
});

function purchaseOrderFor(object $test, \App\Models\SupplyChain\Supplier $supplier, ?\App\Models\SupplyChain\HistoricSupplierProduct $orderedProduct = null): \App\Models\Procurement\PurchaseOrder
{
    $organisation = createOrganisation();
    $orgSupplier  = \App\Models\Procurement\OrgSupplier::where('organisation_id', $organisation->id)->where('supplier_id', $supplier->id)->first()
        ?? \App\Actions\Procurement\OrgSupplier\StoreOrgSupplier::make()->action($organisation, $supplier);

    $purchaseOrder = \App\Actions\Procurement\PurchaseOrder\StorePurchaseOrder::make()->action(
        $orgSupplier,
        [...\App\Models\Procurement\PurchaseOrder::factory()->definition(), 'reference' => 'PD-PO-'.Str::upper(Str::random(6))],
        strict: false
    );

    if ($orderedProduct) {
        $stock    = \App\Actions\Goods\Stock\StoreStock::make()->action($test->group, [...\App\Models\Goods\Stock::factory()->definition(), 'state' => \App\Enums\Goods\Stock\StockStateEnum::ACTIVE]);
        $orgStock = \App\Actions\Inventory\OrgStock\StoreOrgStock::make()->action($organisation, $stock);
        \App\Actions\Procurement\PurchaseOrderTransaction\StorePurchaseOrderTransaction::make()->action($purchaseOrder, $orderedProduct, $orgStock, \App\Models\Procurement\PurchaseOrderTransaction::factory()->definition());
    }

    return $purchaseOrder->fresh();
}

test('a trade unit and its family show their developments in a Development tab, only to people who may see them', function () {
    $productDevelopment = storeDevelopmentWithNewSkus($this, 1);
    $tradeUnit          = $productDevelopment->items->first()->tradeUnit;
    $family             = \App\Actions\Goods\TradeUnitFamily\StoreTradeUnitFamily::make()->action($this->group, ['code' => 'TUF-'.Str::upper(Str::random(5)), 'name' => 'Candles family']);
    $tradeUnit->update(['trade_unit_family_id' => $family->id]);

    get(route('grp.trade_units.units.show', [$tradeUnit->slug, 'tab' => 'product_developments']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Goods/TradeUnit')
            ->where('tabs.navigation.product_developments.number', 1)
            ->where('product_developments.rows.0.code', $productDevelopment->code)
            ->where('product_developments.rows.0.route.parameters.productDevelopment', $productDevelopment->slug));

    get(route('grp.trade_units.families.show', [$family->slug, 'tab' => 'product_developments']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Goods/TradeUnitFamily')
            ->where('product_developments.rows', fn ($rows) => collect($rows)->pluck('code')->contains($productDevelopment->code)));

    $outsider = newGuestUser($this);
    expect(\App\Actions\Goods\ProductDevelopment\UI\GetRelatedProductDevelopments::make()->forTradeUnit($tradeUnit, $outsider))->toBe([])
        ->and(\App\Actions\Goods\ProductDevelopment\UI\GetRelatedProductDevelopments::make()->forTradeUnitFamily($family, $outsider))->toBe([]);
});

test('the supplier and agent pages list only the open developments the viewer may see', function () {
    $open      = developmentWithSupplier($this);
    $supplier  = $open->supplier;
    $cancelled = StoreProductDevelopment::make()->action($this->group, $this->user, [
        'name'        => 'Cancelled '.Str::upper(Str::random(5)),
        'type'        => 'new',
        'supplier_id' => $supplier->id,
        'items'       => [['code' => 'CX-'.Str::upper(Str::random(5)), 'name' => 'Cancelled SKU']],
    ]);
    $cancelled->update(['status' => ProductDevelopmentStatusEnum::CANCELLED]);

    get(route('grp.supply-chain.suppliers.show', [$supplier->slug]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('SupplyChain/Supplier')
            ->where('productDevelopments.rows', fn ($rows) => collect($rows)->pluck('code')->all() === [$open->code]));

    get(route('grp.supply-chain.agents.show', [$supplier->agent->slug]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('SupplyChain/Agent')
            ->where('productDevelopments.rows', fn ($rows) => collect($rows)->pluck('code')->all() === [$open->code]));

    $outsider = newGuestUser($this);
    expect(\App\Actions\Goods\ProductDevelopment\UI\GetRelatedProductDevelopments::make()->forSupplier($supplier, $outsider))->toBe([])
        ->and(\App\Actions\Goods\ProductDevelopment\UI\GetRelatedProductDevelopments::make()->forAgent($supplier->agent, $outsider))->toBe([]);
});

test('a purchase order ordering a development\'s SKU links on its own, and others are linked and unlinked by hand', function () {
    $productDevelopment = developmentWithSupplier($this);
    $item               = $productDevelopment->items->first();
    LinkProductDevelopmentSupplierProduct::make()->action($item, $this->user, ['code' => 'SP-'.Str::upper(Str::random(6)), 'cost' => 1.25, 'units_per_pack' => 1, 'units_per_carton' => 12]);
    $supplierProduct = $item->fresh()->supplierProduct;

    $ordering = purchaseOrderFor($this, $productDevelopment->supplier, $supplierProduct->historicSupplierProduct);
    $other    = purchaseOrderFor($this, $productDevelopment->supplier);
    $links    = fn () => collect(\App\Actions\Goods\ProductDevelopment\ProductDevelopmentPurchaseOrders::rows($productDevelopment->fresh()))->pluck('linked', 'reference')->all();

    expect($links())->toBe([$ordering->reference => 'auto']);

    \App\Actions\Goods\ProductDevelopment\LinkProductDevelopmentPurchaseOrder::make()->action($productDevelopment, $this->user, ['purchase_order_ids' => [$other->id], 'operation' => 'link']);
    expect($links())->toEqualCanonicalizing([$ordering->reference => 'auto', $other->reference => 'manual'])
        ->and(collect(GetProductDevelopmentActivity::make()->handle($productDevelopment->fresh()))->pluck('sentence')->implode(' '))->toContain($other->reference);

    get(route('grp.product_developments.show', [$productDevelopment->slug]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('header.number_purchase_orders', 2)
            ->where('header.purchase_orders.link.route.name', 'grp.models.product_development.purchase_order'));

    get(route('grp.org.procurement.purchase_orders.show', [$other->organisation->slug, $other->slug]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Procurement/PurchaseOrder')
            ->where('productDevelopments.0.code', $productDevelopment->code)
            ->where('productDevelopments.0.linked', 'manual'));
    expect(collect(\App\Actions\Goods\ProductDevelopment\UI\GetRelatedProductDevelopments::make()->forPurchaseOrder($ordering, $this->user))->pluck('linked')->all())->toBe(['auto'])
        ->and(\App\Actions\Goods\ProductDevelopment\UI\GetRelatedProductDevelopments::make()->forPurchaseOrder($ordering, newGuestUser($this)))->toBe([]);

    patch(route('grp.models.product_development.purchase_order', [$productDevelopment->id]), ['purchase_order_ids' => [$other->id], 'operation' => 'unlink'])->assertRedirect();
    expect($links())->toBe([$ordering->reference => 'auto']);

    $otherSupplier = \App\Actions\SupplyChain\Supplier\StoreSupplier::make()->action($productDevelopment->agent, \App\Models\SupplyChain\Supplier::factory()->definition());
    $foreign       = purchaseOrderFor($this, $otherSupplier);
    expect(fn () => \App\Actions\Goods\ProductDevelopment\LinkProductDevelopmentPurchaseOrder::make()->action($productDevelopment, $this->user, ['purchase_order_ids' => [$foreign->id], 'operation' => 'link']))
        ->toThrow(ValidationException::class);
});

test('a purchase order is raised from the development for an organisation buying from its supplier, and opens linked to it', function () {
    $productDevelopment = developmentWithSupplier($this);
    $open               = purchaseOrderFor($this, $productDevelopment->supplier);
    LinkProductDevelopmentSupplierProduct::make()->action($productDevelopment->items->first(), $this->user, ['code' => 'SP-'.Str::upper(Str::random(6)), 'cost' => 1.25, 'units_per_pack' => 1, 'units_per_carton' => 12]);
    $orgSupplier = \App\Models\Procurement\OrgSupplier::where('supplier_id', $productDevelopment->supplier_id)->where('organisation_id', $open->organisation_id)->first();
    $store       = fn () => post(route('grp.models.product_development.purchase_order.store', [$productDevelopment->id]), ['org_supplier_ids' => [$orgSupplier->id]]);

    get(route('grp.product_developments.show', [$productDevelopment->slug]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('header.purchase_orders.link.create.options', fn ($options) => collect($options)->pluck('value')->contains($orgSupplier->id)));

    $store()->assertSessionHasErrors(['org_supplier_ids' => $orgSupplier->organisation->name.': '.__('There is already an open purchase order (:reference). Add the products to it, or submit or delete it before creating a new one.', ['reference' => $open->reference])]);

    $open->update(['state' => \App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum::SUBMITTED]);
    $before = \App\Models\Procurement\PurchaseOrder::where('supplier_id', $productDevelopment->supplier_id)->count();
    $store()->assertSessionHasNoErrors()->assertRedirect();

    $raised = \App\Models\Procurement\PurchaseOrder::where('supplier_id', $productDevelopment->supplier_id)->latest('id')->first();
    expect(\App\Models\Procurement\PurchaseOrder::where('supplier_id', $productDevelopment->supplier_id)->count())->toBe($before + 1)
        ->and(collect(\App\Actions\Goods\ProductDevelopment\ProductDevelopmentPurchaseOrders::rows($productDevelopment->fresh()))->firstWhere('reference', $raised->reference)['linked'] ?? null)->toBe('manual')
        ->and(collect(GetProductDevelopmentActivity::make()->handle($productDevelopment->fresh()))->pluck('sentence')->implode(' '))->toContain('Raised purchase order '.$raised->reference);

    $withoutSupplier = storeDevelopmentWithNewSkus($this, 1);
    post(route('grp.models.product_development.purchase_order.store', [$withoutSupplier->id]), ['org_supplier_ids' => [$orgSupplier->id]])->assertSessionHasErrors('org_supplier_ids');
});

test('the rail shows a developments badge with what waits on the person, and nothing for someone not taking part', function () {
    [$productDevelopment, $people] = developmentWithDesignTeam($this, 1);
    $badge                         = \App\Actions\Goods\ProductDevelopment\ProductDevelopmentWaitingOn::railBadge($people['agent']);

    expect($badge['waiting'])->toBeGreaterThan(0)
        ->and(\App\Actions\Goods\ProductDevelopment\ProductDevelopmentWaitingOn::railBadge(newGuestUser($this)))->toBeNull()
        ->and(\App\Actions\UI\Grp\GetFirstLoadProps::run($people['agent'])['development_badges'])->toBe($badge);

    actingAs($people['agent']);
    foreach (['grp.product_developments.index', 'grp.product_developments.waiting_on_me'] as $page) {
        get(route($page))->assertInertia(fn (AssertableInertia $inertia) => $inertia->where('viewSwitch.2.count', $badge['waiting']));
    }
    $list = get(route('grp.product_developments.json.waiting_badge'))->assertOk()->json();
    expect($list['total'])->toBe($badge['waiting'])
        ->and(collect($list['rows'])->pluck('development.code'))->toContain($productDevelopment->code);

    patch(route('grp.models.profile.update'), ['rail_hidden_badges' => ['developments']]);
    expect($people['agent']->fresh()->settings['rail_hidden_badges'] ?? [])->toBe(['developments']);
});

test('a sent reminder makes the reminded person\'s rail badge pulse until they open it', function () {
    [$productDevelopment, $people] = developmentWithDesignTeam($this, 1);
    $agent                         = $people['agent'];
    $productDevelopment->update(['current_position' => 2]);
    \Illuminate\Support\Facades\Event::fake([\App\Events\BroadcastProductDevelopmentBadgeUpdate::class]);

    expect(\App\Actions\Goods\ProductDevelopment\ProductDevelopmentWaitingOn::railBadge($agent->fresh())['reminded'])->toBeFalse();

    \App\Actions\Goods\ProductDevelopment\RemindProductDevelopmentMembers::make()->action($productDevelopment, $this->user);

    expect(\App\Actions\Goods\ProductDevelopment\ProductDevelopmentWaitingOn::railBadge($agent->fresh())['reminded'])->toBeTrue()
        ->and(\App\Actions\Goods\ProductDevelopment\ProductDevelopmentWaitingOn::railBadge($this->user->fresh())['reminded'] ?? false)->toBeFalse();
    \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\BroadcastProductDevelopmentBadgeUpdate::class, fn ($event) => $event->userId === $agent->id && $event->developmentBadges['reminded'] === true);

    actingAs($agent);
    expect(get(route('grp.product_developments.json.waiting_badge'))->assertOk()->json('reminded'))->toBeFalse()
        ->and(\App\Actions\Goods\ProductDevelopment\ProductDevelopmentWaitingOn::railBadge($agent->fresh())['reminded'])->toBeFalse();
});

test('send reminders only chases the roles of the current stage, not a stage that has not started', function () {
    $productDevelopment = storeDevelopmentWithNewSkus($this, 1);
    $remindable         = \App\Actions\Goods\ProductDevelopment\RemindProductDevelopmentMembers::remindable($productDevelopment->fresh());
    $byRole             = (new \App\Actions\Goods\ProductDevelopment\ProductDevelopmentWaitingOn())->byRole($productDevelopment->fresh());

    expect($productDevelopment->fresh()->current_position)->toBe(1)
        ->and(array_keys($byRole))->toContain(ProductDevelopmentMemberRoleEnum::AGENT->value)
        ->and(array_keys($remindable))->not->toContain(ProductDevelopmentMemberRoleEnum::AGENT->value)
        ->and(array_keys($remindable))->not->toContain(ProductDevelopmentMemberRoleEnum::PURCHASING->value)
        ->and(array_keys($remindable))->not->toContain(ProductDevelopmentMemberRoleEnum::COMPLIANCE->value)
        ->and(array_keys($remindable))->not->toContain(ProductDevelopmentMemberRoleEnum::DESIGNER->value);
});

test('development notifications go to Slack and browser push by default, and a saved choice still wins', function () {
    $user = newGuestUser($this);

    foreach (\App\Enums\SysAdmin\User\UserNotificationEnum::PRODUCT_DEVELOPMENT_EVENTS as $event) {
        expect($event->channelsFor($user))->toBe(['slack', 'browser']);
    }

    $user->update(['settings' => [...($user->settings ?? []), 'notifications' => ['product_development_task' => ['email']]]]);
    expect(\App\Enums\SysAdmin\User\UserNotificationEnum::PRODUCT_DEVELOPMENT_TASK->channelsFor($user->fresh()))->toBe(['email']);

    Config::set('services.webpush.public_key', 'test-public-key');
    $productDevelopment = storeDevelopmentWithNewSkus($this, 1);
    get(route('grp.product_developments.show', [$productDevelopment->slug]))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('push.public_key', 'test-public-key'));

    $settings = $this->user->settings ?? [];
    $this->user->update(['settings' => [...$settings, 'notifications' => ['product_development_task' => ['slack']]]]);
    get(route('grp.product_developments.show', [$productDevelopment->slug]))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('push', null));
    $this->user->update(['settings' => $settings]);
});

test('picking a member warns what their permissions will stop them doing, and nothing for someone with full access', function () {
    $outsider = newGuestUser($this);
    $check    = fn (\App\Models\SysAdmin\User $user, ?string $role) => get(route('grp.product_developments.json.member_access', ['user_id' => $user->id, 'role' => $role]))->assertOk()->json('warnings');

    expect($check($outsider, ProductDevelopmentMemberRoleEnum::DESIGNER->value))->toHaveCount(1)
        ->and($check($outsider, ProductDevelopmentMemberRoleEnum::PURCHASING->value))->toHaveCount(2)
        ->and($check($this->user, ProductDevelopmentMemberRoleEnum::DESIGNER->value))->toBe([]);
});

test('a design version downloads as a vector PDF at its real size, drawing every item type, and only for people who may see it', function () {
    $designer           = newGuestUser($this);
    $buyer              = newGuestUser($this);
    $agent              = newAgentUser($this);
    $productDevelopment = storeDevelopmentWithNewSkus($this, 1);
    SyncProductDevelopmentMembers::make()->action($productDevelopment, ['members' => [
        ['user_id' => $designer->id, 'role' => 'designer'],
        ['user_id' => $buyer->id, 'role' => 'buyer'],
        ['user_id' => $agent->id, 'role' => 'agent'],
    ]]);
    approveDesign($productDevelopment, 'concept', ['designer' => $designer, 'buyer' => $buyer, 'agent' => $agent]);

    $version  = StoreProductDevelopmentDesignVersion::make()->action($productDevelopment, $designer, ['round' => 'packaging']);
    $elements = [
        ['id' => 'text', 'type' => 'text', 'x' => 2, 'y' => 2, 'w' => 40, 'h' => 8, 'text' => 'Incense', 'font_size' => 12, 'rotate' => 15],
        ['id' => 'image', 'type' => 'image', 'x' => 2, 'y' => 12, 'w' => 10, 'h' => 10],
        ['id' => 'shape', 'type' => 'shape', 'shape' => 'ellipse', 'x' => 14, 'y' => 12, 'w' => 10, 'h' => 10, 'fill' => 'radial:#fde047:#16a34a', 'stroke' => '#111827', 'stroke_px' => 1],
        ['id' => 'data', 'type' => 'data', 'source' => 'product_name', 'x' => 26, 'y' => 12, 'w' => 30, 'h' => 8],
        ['id' => 'pictogram', 'type' => 'pictogram', 'source' => 'pictogram_flammable', 'x' => 2, 'y' => 24, 'w' => 10, 'h' => 10],
        ['id' => 'barcode', 'type' => 'barcode', 'source' => 'unit', 'x' => 14, 'y' => 24, 'w' => 20, 'h' => 10],
        ['id' => 'zone', 'type' => 'zone', 'zone' => 'compliance', 'x' => 36, 'y' => 24, 'w' => 20, 'h' => 10],
    ];
    $layout = [...$version->data['layout'], 'width' => 60, 'length' => 50, 'height' => 40];
    $layout['faces']['front']['elements'] = $elements;
    UpdateProductDevelopmentDesignVersion::make()->action($version, $designer, ['layout' => $layout]);

    preg_match('/export type ElementType = ([^\n]+)/', file_get_contents(resource_path('js/Components/Goods/ProductDevelopment/Design/boxNet.ts')), $editorTypes);
    preg_match_all('/"(\w+)"/', $editorTypes[1], $editorTypeNames);
    expect(\App\Actions\Goods\ProductDevelopment\Design\ProductDevelopmentDesignPdf::ELEMENT_TYPES)->toEqualCanonicalizing($editorTypeNames[1])
        ->and(collect($elements)->pluck('type')->all())->toEqualCanonicalizing(\App\Actions\Goods\ProductDevelopment\Design\ProductDevelopmentDesignPdf::ELEMENT_TYPES)
        ->and(\App\Actions\Goods\ProductDevelopment\Design\ProductDevelopmentDesignLayout::netSize($layout))->toEqual(['width' => 235, 'height' => 190]);

    $parameters = ['productDevelopment' => $productDevelopment->slug, 'designRound' => $version->id];
    $response   = get(route('grp.product_developments.design.pdf', $parameters))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    $pdf        = $response->getContent();
    preg_match('/\/MediaBox\s*\[\s*0\s+0\s+([\d.]+)\s+([\d.]+)\s*\]/', $pdf, $mediaBox);
    expect(str_starts_with($pdf, '%PDF'))->toBeTrue()
        ->and(round((float) $mediaBox[1] / 72 * 25.4))->toEqual(235.0)
        ->and(round((float) $mediaBox[2] / 72 * 25.4))->toEqual(190.0);

    actingAs(newGuestUser($this));
    get(route('grp.product_developments.design.pdf', $parameters))->assertForbidden();
});
