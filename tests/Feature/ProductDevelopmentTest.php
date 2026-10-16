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
use App\Actions\Goods\ProductDevelopment\StoreProductDevelopment;
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

function storeDevelopmentWithNewSkus(object $test, int $numberSkus = 4): ProductDevelopment
{
    $suffix = Str::upper(Str::random(5));

    return StoreProductDevelopment::make()->action($test->group, $test->user, [
        'name'    => 'Lavender candles '.$suffix,
        'type'    => 'new',
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
            ->has('viewSwitch', 2)
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
            ->where('overview.waiting_on', fn ($rows) => count($rows) > 0)
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
    $agent      = newGuestUser($this);
    $compliance = newGuestUser($this);

    $productDevelopment = StoreProductDevelopment::make()->action($this->group, $this->user, [
        'name'    => 'Tasks check '.Str::upper(Str::random(5)),
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
    $agent      = newGuestUser($this);
    $productDevelopment = StoreProductDevelopment::make()->action($this->group, $this->user, [
        'name'    => 'Artwork check '.Str::upper(Str::random(5)),
        'type'    => 'new',
        'members' => [
            ['user_id' => $purchasing->id, 'role' => ProductDevelopmentMemberRoleEnum::PURCHASING->value],
            ['user_id' => $agent->id, 'role' => ProductDevelopmentMemberRoleEnum::AGENT->value],
        ],
        'items'   => [['code' => 'AW-'.Str::upper(Str::random(5)), 'name' => 'Artwork SKU']],
    ]);
    $productDevelopment->checklistItems()->update(['state' => ProductDevelopmentChecklistItemStateEnum::NOT_APPLICABLE->value, 'na_reason' => 'test']);
    $productDevelopment->update(['current_position' => ProductDevelopmentStageEnum::FINAL_APPROVAL->position()]);
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
    $agent              = newGuestUser($this);
    $productDevelopment = StoreProductDevelopment::make()->action($this->group, $this->user, [
        'name'    => 'Reminder check '.Str::upper(Str::random(5)),
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

    $firstAgent  = newGuestUser($this);
    $secondAgent = newGuestUser($this);
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
