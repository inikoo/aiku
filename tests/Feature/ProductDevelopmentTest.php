<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-17h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

/** @noinspection PhpUnhandledExceptionInspection */

use App\Actions\Goods\ProductDevelopment\GetProductDevelopmentStageGate;
use App\Actions\Goods\ProductDevelopment\Hydrators\ProductDevelopmentHydrateChecklist;
use App\Actions\Goods\ProductDevelopment\MoveProductDevelopmentToNextStage;
use App\Actions\Goods\ProductDevelopment\StoreProductDevelopment;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentChecklistItemStateEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStageEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStageStateEnum;
use App\Enums\Goods\TradeUnit\TradeUnitStatusEnum;
use App\Models\Goods\ProductDevelopment;
use App\Actions\UI\Grp\Layout\GetGroupNavigation;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
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
            ->where('columns.0.cards', fn ($cards) => collect($cards)->contains(fn ($card) => $card['code'] === $productDevelopment->code && $card['number_required_missing'] === $missing)));
});

test('the development page shows the header, stage bar and a blocked move with its reason', function () {
    $productDevelopment = storeDevelopmentWithNewSkus($this, 2);

    get(route('grp.product_developments.show', ['productDevelopment' => $productDevelopment->slug]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Goods/ProductDevelopment')
            ->has('breadcrumbs', 3)
            ->has('header')
            ->has('stages', ProductDevelopmentStageEnum::lastPosition())
            ->where('stageMove.can_move', false)
            ->where('stageMove.reason', fn ($reason) => str_contains($reason, __('Buyer')))
            ->where('tabs.current', 'brief')
            ->has('brief.members', 1)
            ->has('tabs.navigation', 7));
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
