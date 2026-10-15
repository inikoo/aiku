<?php

use App\Actions\SupplyChain\UI\GetPurchaseOrderJourney;
use Illuminate\Support\Carbon;

function journeyFor(array $facts, string $today = '2026-09-24'): array
{
    return GetPurchaseOrderJourney::run(array_merge([
        'journey'        => 'agent',
        'state'          => 'submitted',
        'delivery_state' => 'in_process',
        'created_at'     => '2026-09-01',
        'submitted_at'   => '2026-09-02',
    ], $facts), Carbon::parse($today));
}

function segmentOf(array $journey, string $key): ?array
{
    return collect($journey['segments'])->firstWhere('key', $key);
}

it('runs an agent order nobody tracks on the dates the system records, with the agent stages only planned', function () {
    $journey = journeyFor(['delivery_time' => 90]);

    expect($journey['current_stage'])->toBe('shipped')
        ->and($journey['status'])->toBe('on_track')
        ->and(segmentOf($journey, 'in_production')['state'])->toBe('untracked')
        ->and(segmentOf($journey, 'ready')['state'])->toBe('untracked')
        ->and(Carbon::parse('2026-09-02')->diffInDays(Carbon::parse(segmentOf($journey, 'received')['planned_at'])))->toBe(91.0);
});

it('holds a tracked agent order at the first stage nobody marked and counts the days it is late', function () {
    $journey = journeyFor([
        'deposit_paid_at' => '2026-07-01',
        'stage_days'      => ['in_production' => 30],
        'created_at'      => '2026-06-20',
        'submitted_at'    => '2026-06-25',
    ]);

    expect($journey['current_stage'])->toBe('in_production')
        ->and($journey['status'])->toBe('overdue')
        ->and($journey['days_overdue'])->toBe(55)
        ->and(segmentOf($journey, 'in_production')['planned_at'])->toBe('2026-07-31');
});

it('marks earlier stages as done without a date once a later stage happened', function () {
    $journey = journeyFor(['delivery_state' => 'dispatched', 'dispatched_at' => '2026-09-20', 'deposit_paid_at' => '2026-09-05']);

    expect(segmentOf($journey, 'in_production')['state'])->toBe('skipped')
        ->and(segmentOf($journey, 'ready')['state'])->toBe('skipped')
        ->and($journey['current_stage'])->toBe('arrived');
});

it('turns a draft that was never sent red at PO submitted', function () {
    $journey = journeyFor(['journey' => 'supplier', 'state' => 'in_process', 'submitted_at' => null, 'created_at' => '2026-09-01']);

    expect($journey['current_stage'])->toBe('submitted')
        ->and($journey['status'])->toBe('overdue')
        ->and($journey['days_overdue'])->toBe(20)
        ->and(segmentOf($journey, 'deposit_paid'))->toBeNull()
        ->and(segmentOf($journey, 'in_production'))->toBeNull();
});

it('shows production for agent orders and for suppliers that take production days', function () {
    expect(segmentOf(journeyFor([]), 'in_production'))->not->toBeNull()
        ->and(segmentOf(journeyFor(['journey' => 'supplier']), 'in_production'))->toBeNull()
        ->and(segmentOf(journeyFor(['journey' => 'supplier', 'production_waiting_time' => 10]), 'in_production'))->not->toBeNull();
});

it('completes once every product from the order is online', function () {
    $facts = [
        'journey'           => 'partner',
        'state'             => 'settled',
        'delivery_state'    => 'placed',
        'dispatched_at'     => '2026-09-10',
        'received_at'       => '2026-09-12',
        'placed_at'         => '2026-09-13',
        'sellable_products' => 2,
        'online_at'         => '2026-09-15 10:00:00',
    ];

    $waiting  = journeyFor([...$facts, 'online_products' => 1]);
    $complete = journeyFor([...$facts, 'online_products' => 2]);

    expect($waiting['current_stage'])->toBe('products_online')
        ->and($complete['status'])->toBe('completed')
        ->and($complete['eta'])->toBe('2026-09-15');
});

it('warns three days before a target', function () {
    $journey = journeyFor(['journey' => 'supplier', 'estimated_received_at' => '2026-09-28', 'delivery_state' => 'dispatched', 'dispatched_at' => '2026-09-20']);

    expect($journey['current_stage'])->toBe('arrived')
        ->and($journey['status'])->toBe('at_risk');
});

it('keeps the agreed plan on later stages and moves only the estimate when a stage runs late', function () {
    $journey = journeyFor([
        'journey'       => 'supplier',
        'created_at'    => '2026-06-01',
        'submitted_at'  => '2026-06-01',
        'delivery_time' => 21,
    ]);

    $received = segmentOf($journey, 'received');

    expect($journey['current_stage'])->toBe('shipped')
        ->and($journey['status'])->toBe('overdue')
        ->and($received['planned_at'])->toBe('2026-06-22')
        ->and($received['behind_plan'])->toBeTrue()
        ->and($received['forecast_at'] > '2026-09-24')->toBeTrue()
        ->and($journey['eta'] > '2026-09-24')->toBeTrue();
});

it('does not count a draft as sent even when it carries a submitted date', function () {
    $journey = journeyFor(['journey' => 'partner', 'state' => 'in_process', 'submitted_at' => '2026-09-02', 'created_at' => '2026-09-01']);

    expect(segmentOf($journey, 'submitted')['state'])->toBe('overdue')
        ->and(segmentOf($journey, 'submitted')['done_at'])->toBeNull();
});

it('targets QC on the approved ready date and shipping, after the clean handover, seven days after it', function () {
    $journey = journeyFor(['deposit_paid_at' => '2026-09-05', 'approved_ready_at' => '2026-10-20']);

    expect(segmentOf($journey, 'ready')['planned_at'])->toBe('2026-10-20')
        ->and(segmentOf($journey, 'shipped')['planned_at'])->toBe('2026-10-27');
});

it('works the shipping target back from a recorded arrival date', function () {
    $journey = journeyFor([
        'journey'               => 'partner',
        'created_at'            => '2026-08-01',
        'submitted_at'          => '2026-08-01',
        'estimated_received_at' => '2026-09-30',
    ]);

    expect(segmentOf($journey, 'shipped')['planned_at'])->toBe('2026-09-26')
        ->and($journey['status'])->toBe('at_risk');
});

it('counts the goods as arrived once every delivery reached the port', function () {
    $journey = journeyFor(['journey' => 'supplier', 'delivery_state' => 'dispatched', 'dispatched_at' => '2026-09-10', 'arrived' => true, 'arrived_at' => '2026-09-20']);

    expect(segmentOf($journey, 'arrived')['state'])->toBe('done')
        ->and(segmentOf($journey, 'arrived')['done_at'])->toBe('2026-09-20')
        ->and($journey['current_stage'])->toBe('received');
});

it('leaves the online date empty when the date the goods were placed is unknown', function () {
    $journey = journeyFor([
        'journey'           => 'supplier',
        'state'             => 'settled',
        'delivery_state'    => 'placed',
        'sellable_products' => 1,
        'online_products'   => 1,
        'online_at'         => '2019-11-07',
    ]);

    expect(segmentOf($journey, 'products_online')['state'])->toBe('done')
        ->and(segmentOf($journey, 'products_online')['done_at'])->toBeNull();
});
