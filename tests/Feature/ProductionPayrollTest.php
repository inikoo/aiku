<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 25 Nov 2024 11:48:10 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

/** @noinspection PhpUnhandledExceptionInspection */

namespace Tests\Feature;

use App\Actions\Production\Artefact\StoreArtefact;
use App\Actions\Production\ArtefactDepartment\StoreArtefactDepartment;
use App\Actions\Production\Artefact\SetArtefactsRecipe;
use App\Actions\Production\Artisan\AttachArtisan;
use App\Actions\Production\Artisan\DetachArtisan;
use App\Actions\Production\Artisan\ToggleArtisanInRoster;
use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Models\HumanResources\Employee;
use App\Actions\Production\JobOrder\ConfirmJobOrder;
use App\Actions\Production\JobOrder\StoreJobOrder;
use App\Actions\Production\ManufactureTask\StoreManufactureTask;
use App\Actions\Production\Artefact\AttachManufactureTaskToArtefact;
use App\Actions\Production\JobOrderItem\StoreJobOrderItem;
use App\Actions\Production\ManufactureTaskSession\CloseManufactureTaskSession;
use App\Actions\Production\ManufactureTaskSession\StartManufactureTaskSession;
use App\Actions\Production\ManufactureTaskSession\VoidManufactureTaskSession;
use App\Enums\Production\JobOrderItemTask\JobOrderItemTaskStateEnum;
use App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum;
use App\Actions\Production\Production\StoreProduction;
use App\Actions\Production\RawMaterial\StoreRawMaterial;
use App\Enums\Production\ManufactureTask\ManufactureTaskOperativeRewardAllowanceTypeEnum;
use App\Enums\Production\ManufactureTask\ManufactureTaskOperativeRewardTermsEnum;
use App\Enums\Production\RawMaterial\RawMaterialStateEnum;
use App\Enums\Production\RawMaterial\RawMaterialTypeEnum;
use App\Enums\Production\RawMaterial\RawMaterialUnitEnum;
use App\Models\Production\Artefact;
use App\Models\Production\ManufactureTaskSession;
use App\Models\Production\ManufactureTask;
use App\Models\Production\Production;
use App\Models\Production\RawMaterial;
use Config;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

require_once __DIR__.'/../Support/ProductionHelpers.php';

beforeAll(function () {
    loadDB();
});

beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->group        = group();
    setPermissionsTeamId($this->group->id);
    $this->guest        = createAdminGuest($this->group);
    ManufactureTaskSession::where('user_id', $this->guest->getUser()->id)
        ->where('state', ManufactureTaskSessionStateEnum::OPEN)
        ->update(['state' => ManufactureTaskSessionStateEnum::VOIDED]);

    $production = Production::orderBy('id')->first();
    if (!$production) {
        data_set($storeData, 'code', 'CODE');
        data_set($storeData, 'name', 'NAME');

        $production = StoreProduction::make()->action(
            $this->organisation,
            $storeData
        );
    }
    $this->production = $production;

    $artefact = Artefact::orderBy('id')->first();
    if (!$artefact) {
        data_set($storeData, 'code', 'CODE');
        data_set($storeData, 'name', 'NAME');

        $artefact = StoreArtefact::make()->action(
            $this->production,
            $storeData
        );
    }
    $this->artefact = $artefact;

    $rawMaterial = RawMaterial::orderBy('id')->first();
    if (!$rawMaterial) {
        data_set($storeData, 'type', RawMaterialTypeEnum::CONSUMABLE->value);
        data_set($storeData, 'state', RawMaterialStateEnum::ORPHAN->value);
        data_set($storeData, 'code', 'CODE');
        data_set($storeData, 'description', 'desc');
        data_set($storeData, 'unit', RawMaterialUnitEnum::KILOGRAM->value);
        data_set($storeData, 'unit_cost', 10);

        $rawMaterial = StoreRawMaterial::make()->action(
            $this->production,
            $storeData
        );
    }
    $this->rawMaterial = $rawMaterial;

    $manufactureTask = ManufactureTask::where('code', '!=', 'PROD')->orderBy('id')->first();
    if (!$manufactureTask) {
        data_set($storeData, 'code', 'CODE');
        data_set($storeData, 'name', 'name');
        data_set($storeData, 'task_materials_cost', 10);
        data_set($storeData, 'task_energy_cost', 10);
        data_set($storeData, 'task_other_cost', 10);
        data_set($storeData, 'task_work_cost', 10);
        data_set($storeData, 'task_lower_target', 10);
        data_set($storeData, 'task_upper_target', 10);
        data_set($storeData, 'operative_reward_terms', ManufactureTaskOperativeRewardTermsEnum::ABOVE_LOWER_LIMIT->value);
        data_set($storeData, 'operative_reward_allowance_type', ManufactureTaskOperativeRewardAllowanceTypeEnum::OFFSET_SALARY->value);
        data_set($storeData, 'operative_reward_amount', 10);

        $manufactureTask = StoreManufactureTask::make()->action(
            $this->production,
            $storeData
        );
    }
    $this->manufactureTask = $manufactureTask;
    $this->artisan('group:seed_aiku_scoped_sections')->assertExitCode(0);

    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->guest->getUser());

});

test('UI show artisans dashboard', function () {
    $response = get(route('grp.org.productions.show.artisans.dashboard', [
        $this->organisation->slug,
        $this->production->slug,
    ]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Production/ArtisansDashboard')
            ->has('artisans')
            ->missing('payroll_export_route')
            ->has('breadcrumbs', 3);
    });
});

test('UI show manufacture payroll', function () {
    $response = get(route('grp.org.productions.show.artisans.payroll', [
        $this->organisation->slug,
        $this->production->slug,
    ]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Production/ManufacturePayroll')
            ->has('payroll_export_route')
            ->where('breadcrumbs', fn ($breadcrumbs) => collect($breadcrumbs)->last()['simple']['route']['name'] === 'grp.org.productions.show.artisans.payroll');
    });
});

test('UI index job orders', function () {
    $response = get(route('grp.org.productions.show.operations.job-orders.index', [
        $this->organisation->slug,
        $this->production->slug,
    ]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Production/JobOrders')
            ->has('data')
            ->has('pageHead')
            ->has('breadcrumbs');
    });
});

test('UI show job order', function () {
    $jobOrder = StoreJobOrder::make()->action($this->production, []);
    $response = get(route('grp.org.productions.show.operations.job-orders.show', [
        $this->organisation->slug,
        $this->production->slug,
        $jobOrder->slug,
    ]));
    $response->assertInertia(function (AssertableInertia $page) use ($jobOrder) {
        $page
            ->component('Org/Production/JobOrder')
            ->where('job_order.reference', $jobOrder->reference)
            ->has('items')
            ->has('artefact_options');
    });
});

test('job order prints a job list pdf', function () {
    $jobOrder = StoreJobOrder::make()->action($this->production, []);
    get(route('grp.org.productions.show.operations.job-orders.pdf', [
        $this->organisation->slug,
        $this->production->slug,
        $jobOrder->slug,
    ]))->assertOk()->assertHeader('Content-Type', 'application/pdf');
});

test('payroll csv export aggregates closed sessions with snapshotted rates', function () {
    $this->artefact->manufactureTasks()->sync([
        $this->manufactureTask->id => ['position' => 1, 'units_per_artefact' => 1],
    ]);
    $jobOrder = StoreJobOrder::make()->action($this->production, []);
    $item     = StoreJobOrderItem::make()->action($jobOrder, ['artefact_id' => $this->artefact->id, 'quantity' => 5]);
    ConfirmJobOrder::make()->action($jobOrder);
    CloseManufactureTaskSession::make()->action(
        StartManufactureTaskSession::make()->action($this->guest->getUser(), $item->tasks()->first()),
        ['quantity_made' => 5]
    );

    $response = get(route('grp.org.productions.show.artisans.payroll.export', [
        $this->organisation->slug,
        $this->production->slug,
        'from' => now()->toDateString(),
        'to'   => now()->toDateString(),
    ]));

    $response->assertOk();
    $response->assertDownload();

    ob_start();
    $response->sendContent();
    $csv = ob_get_clean();

    expect($csv)->toContain('"Worker","Task code"')
        ->and($csv)->toContain($this->manufactureTask->code);
});

test('a voided session removes its quantities from the task and payroll', function () {
    $this->artefact->manufactureTasks()->sync([
        $this->manufactureTask->id => ['position' => 1, 'units_per_artefact' => 1],
    ]);
    $user     = $this->guest->getUser();
    $jobOrder = StoreJobOrder::make()->action($this->production, []);
    $item     = StoreJobOrderItem::make()->action($jobOrder, ['artefact_id' => $this->artefact->id, 'quantity' => 20]);
    ConfirmJobOrder::make()->action($jobOrder);

    $task = $item->tasks()->first();
    CloseManufactureTaskSession::make()->action(
        StartManufactureTaskSession::make()->action($user, $task),
        ['quantity_made' => 15]
    );
    $session = CloseManufactureTaskSession::make()->action(
        StartManufactureTaskSession::make()->action($user, $task),
        ['quantity_made' => 5]
    );

    expect($task->refresh()->state)->toBe(JobOrderItemTaskStateEnum::DONE);

    VoidManufactureTaskSession::make()->action($session);

    $task->refresh();
    expect($session->refresh()->state)->toBe(ManufactureTaskSessionStateEnum::VOIDED)
        ->and($task->state)->toBe(JobOrderItemTaskStateEnum::IN_PROGRESS)
        ->and((float)$task->quantity_made)->toBe(15.0);

    expect(fn () => VoidManufactureTaskSession::make()->action($session))
        ->toThrow(ValidationException::class);
});

test('UI index artisans aggregates worker sessions', function () {
    $this->artefact->manufactureTasks()->sync([
        $this->manufactureTask->id => ['position' => 1, 'units_per_artefact' => 1],
    ]);
    $jobOrder = StoreJobOrder::make()->action($this->production, []);
    $jobOrderItem = StoreJobOrderItem::make()->action($jobOrder, [
        'artefact_id' => $this->artefact->id,
        'quantity'    => 5,
    ]);
    ConfirmJobOrder::make()->action($jobOrder);
    $session = StartManufactureTaskSession::make()->action($this->guest->getUser(), $jobOrderItem->tasks()->first());
    CloseManufactureTaskSession::make()->action($session, ['quantity_made' => 5]);

    $response = get(route('grp.org.productions.show.artisans.index', [
        $this->organisation->slug,
        $this->production->slug,
        'from' => now()->toDateString(),
        'to'   => now()->toDateString(),
    ]));
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Org/Production/Artisans')
        ->has('period')
        ->has('manufacture_tasks')
        ->has('artisans.0', fn (AssertableInertia $page) => $page
            ->has('worker')
            ->has('number_sessions')
            ->has('earned')
            ->has('jobs')
            ->etc()));

    $job = collect(collect($response->viewData('page')['props']['artisans'])->firstWhere('user_id', $this->guest->getUser()->id)['jobs'])
        ->firstWhere('job_order_item_id', $jobOrderItem->id);
    expect($job['job_order_reference'])->toBe($jobOrder->reference)
        ->and($job['steps'][0]['task_name'])->toBe($this->manufactureTask->name)
        ->and($job['steps'][0]['quantity_made'])->toEqual(5)
        ->and($job['steps'][0]['sessions'])->toHaveCount(1);
});

test('performance breakdown groups a job with steps from two different operators', function () {
    $secondTask = stepTestManufactureTask($this->production);

    $this->artefact->manufactureTasks()->sync([
        $this->manufactureTask->id => ['position' => 1, 'units_per_artefact' => 1],
        $secondTask->id            => ['position' => 2, 'units_per_artefact' => 1],
    ]);

    $jobOrder     = StoreJobOrder::make()->action($this->production, []);
    $jobOrderItem = StoreJobOrderItem::make()->action($jobOrder, ['artefact_id' => $this->artefact->id, 'quantity' => 5]);
    ConfirmJobOrder::make()->action($jobOrder);

    $firstUser  = $this->guest->getUser();
    $secondUser = stepTestSecondOperator($this->group);

    [$firstTask, $lastTask] = $jobOrderItem->tasks()->orderBy('position')->get();

    CloseManufactureTaskSession::make()->action(
        StartManufactureTaskSession::make()->action($firstUser, $firstTask),
        ['quantity_made' => 5]
    );
    CloseManufactureTaskSession::make()->action(
        StartManufactureTaskSession::make()->action($secondUser, $lastTask),
        ['quantity_made' => 5]
    );

    $props = get(route('grp.org.productions.show.artisans.index', [
        $this->organisation->slug,
        $this->production->slug,
        'from' => now()->toDateString(),
        'to'   => now()->toDateString(),
    ]))->viewData('page')['props'];

    $firstUsersJob = collect(collect($props['artisans'])->firstWhere('user_id', $firstUser->id)['jobs'])->firstWhere('job_order_item_id', $jobOrderItem->id);
    expect($firstUsersJob['job_order_reference'])->toBe($jobOrder->reference)
        ->and(collect($firstUsersJob['steps'])->pluck('task_name')->all())->toBe([$this->manufactureTask->name]);

    $filteredProps = get(route('grp.org.productions.show.artisans.index', [
        $this->organisation->slug,
        $this->production->slug,
        'from'                => now()->toDateString(),
        'to'                  => now()->toDateString(),
        'manufacture_task_id' => $secondTask->id,
    ]))->viewData('page')['props'];

    $filteredArtisans = collect($filteredProps['artisans']);
    expect($filteredArtisans->pluck('user_id')->all())->toContain($secondUser->id)
        ->and($filteredArtisans->flatMap(fn ($artisan) => $artisan['jobs'])->flatMap(fn ($job) => $job['steps'])->pluck('manufacture_task_id')->unique()->values()->all())
        ->toBe([$secondTask->id]);
});

test('performance breakdown keeps two artefacts of one job order apart and takes breaks out of the hours', function () {
    $otherArtefact = Artefact::where('production_id', $this->production->id)->where('code', 'PERF-2')->first()
        ?? StoreArtefact::make()->action($this->production, ['code' => 'PERF-2', 'name' => 'Second artefact']);
    $sameNamedTask = stepTestManufactureTask($this->production, 'SAMENAME', $this->manufactureTask->name);
    $this->artefact->manufactureTasks()->sync([
        $this->manufactureTask->id => ['position' => 1, 'units_per_artefact' => 1],
        $sameNamedTask->id         => ['position' => 2, 'units_per_artefact' => 1],
    ]);
    $otherArtefact->manufactureTasks()->sync([$this->manufactureTask->id => ['position' => 1, 'units_per_artefact' => 1]]);

    $jobOrder  = StoreJobOrder::make()->action($this->production, []);
    $firstItem = StoreJobOrderItem::make()->action($jobOrder, ['artefact_id' => $this->artefact->id, 'quantity' => 4]);
    $otherItem = StoreJobOrderItem::make()->action($jobOrder, ['artefact_id' => $otherArtefact->id, 'quantity' => 6]);
    ConfirmJobOrder::make()->action($jobOrder);

    $user = $this->guest->getUser();
    foreach ($firstItem->tasks()->orderBy('position')->get() as $step) {
        CloseManufactureTaskSession::make()->action(StartManufactureTaskSession::make()->action($user, $step), ['quantity_made' => 4]);
    }
    $session = CloseManufactureTaskSession::make()->action(StartManufactureTaskSession::make()->action($user, $otherItem->tasks()->first()), ['quantity_made' => 6]);
    $session->update(['started_at' => $session->ended_at->copy()->subHours(2), 'break_minutes' => 30]);

    $props = get(route('grp.org.productions.show.artisans.index', [
        $this->organisation->slug,
        $this->production->slug,
        'from' => now()->toDateString(),
        'to'   => now()->toDateString(),
    ]))->viewData('page')['props'];

    $jobs      = collect(collect($props['artisans'])->firstWhere('user_id', $user->id)['jobs']);
    $firstJob  = $jobs->firstWhere('job_order_item_id', $firstItem->id);
    $otherJob  = $jobs->firstWhere('job_order_item_id', $otherItem->id);

    expect($firstJob['artefact_code'])->toBe($this->artefact->code)
        ->and(collect($firstJob['steps'])->pluck('manufacture_task_id')->sort()->values()->all())->toBe(collect([$this->manufactureTask->id, $sameNamedTask->id])->sort()->values()->all())
        ->and(collect($firstJob['steps'])->pluck('quantity_made')->all())->toEqual([4, 4])
        ->and($otherJob['artefact_code'])->toBe($otherArtefact->code)
        ->and($otherJob['job_order_reference'])->toBe($jobOrder->reference)
        ->and($otherJob['steps'][0]['quantity_made'])->toEqual(6)
        ->and($otherJob['steps'][0]['hours'])->toEqual(1.5)
        ->and($otherJob['hours'])->toEqual(1.5);

    $this->artefact->manufactureTasks()->sync([$this->manufactureTask->id => ['position' => 1, 'units_per_artefact' => 1]]);
});

describe('production reward pay bands', function () {
    beforeEach(function () {
        $this->artefact->manufactureTasks()->sync([
            $this->manufactureTask->id => ['position' => 1, 'units_per_artefact' => 1],
        ]);

        $jobOrder     = StoreJobOrder::make()->action($this->production, []);
        $jobOrderItem = StoreJobOrderItem::make()->action($jobOrder, [
            'artefact_id' => $this->artefact->id,
            'quantity'    => 1000,
        ]);
        ConfirmJobOrder::make()->action($jobOrder);
        $this->payBandJobOrderItemTask = $jobOrderItem->tasks()->first();

        foreach ([
            ['code' => '0', 'name' => 'Band 0', 'hourly_rate' => 12.71, 'target_multiplier' => null],
            ['code' => '1', 'name' => 'Band 1', 'hourly_rate' => 13.00, 'target_multiplier' => 1.0228],
            ['code' => '2', 'name' => 'Band 2', 'hourly_rate' => 14.00, 'target_multiplier' => 1.1015],
            ['code' => '3', 'name' => 'Band 3', 'hourly_rate' => 15.00, 'target_multiplier' => 1.1802],
            ['code' => 'D', 'name' => 'Development', 'hourly_rate' => 13.30, 'target_multiplier' => null],
            ['code' => 'DG', 'name' => 'Development Group', 'hourly_rate' => 15.00, 'target_multiplier' => null],
        ] as $bandData) {
            \App\Models\Production\ManufacturePayBand::query()->firstOrCreate(
                [
                    'production_id' => $this->production->id,
                    'code'          => $bandData['code'],
                ],
                array_merge($bandData, [
                    'group_id'        => $this->production->group_id,
                    'organisation_id' => $this->production->organisation_id,
                    'production_id'   => $this->production->id,
                    'effective_from'  => now()->subYear(),
                ])
            );
        }
    });

    function makePayBandSession(
        \App\Models\Production\JobOrderItemTask $jobOrderItemTask,
        float $quantityMade,
        float $hours,
        int $breakMinutes = 0,
        string $activityType = \App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionActivityTypeEnum::PRODUCTION->value
    ): \App\Models\Production\ManufactureTaskSession {
        $startedAt = now();
        $endedAt   = $startedAt->clone()->addSeconds((int) round(($hours + $breakMinutes / 60) * 3600));

        return \App\Models\Production\ManufactureTaskSession::create([
            'group_id'               => $jobOrderItemTask->group_id,
            'organisation_id'        => $jobOrderItemTask->organisation_id,
            'production_id'          => $jobOrderItemTask->production_id,
            'job_order_item_task_id' => $jobOrderItemTask->id,
            'manufacture_task_id'    => $jobOrderItemTask->manufacture_task_id,
            'user_id'                => auth()->user()->id,
            'state'                  => \App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum::CLOSED,
            'started_at'             => $startedAt,
            'ended_at'               => $endedAt,
            'quantity_made'          => $quantityMade,
            'break_minutes'          => $breakMinutes,
            'activity_type'          => $activityType,
        ]);
    }

    test('worked example lands in band 3 with the expected pay and bonus', function () {
        $this->payBandJobOrderItemTask->manufactureTask->update(['standard_rate' => 152]);
        $session = makePayBandSession($this->payBandJobOrderItemTask, 900, 4.9333);

        $session = \App\Actions\Production\ManufactureTaskSession\CalculateManufactureTaskSessionPay::run($session);

        expect($session->band_code)->toBe('3')
            ->and((float) $session->pay)->toBe(74.00)
            ->and((float) $session->bonus)->toBe(11.30)
            ->and((float) $session->units_per_hour)->toBe(182.0);
    });

    test('a closed session with band pay appears in the payroll export with the correct figures', function () {
        $exportTask = ManufactureTask::where('production_id', $this->production->id)->where('code', 'EXPRT1')->first();

        if (!$exportTask) {
            $exportTask = \App\Actions\Production\ManufactureTask\StoreManufactureTask::make()->action(
                $this->production,
                [
                    'code'                             => 'EXPRT1',
                    'name'                             => 'Export task',
                    'task_materials_cost'               => 1,
                    'task_energy_cost'                  => 1,
                    'task_other_cost'                   => 1,
                    'task_work_cost'                    => 1,
                    'task_lower_target'                 => 1,
                    'task_upper_target'                 => 1,
                    'operative_reward_terms'            => ManufactureTaskOperativeRewardTermsEnum::ABOVE_LOWER_LIMIT,
                    'operative_reward_allowance_type'   => ManufactureTaskOperativeRewardAllowanceTypeEnum::OFFSET_SALARY,
                    'operative_reward_amount'           => 1,
                ]
            );
        }
        $exportTask->update(['standard_rate' => 152]);

        $exportArtefact = Artefact::where('production_id', $this->production->id)->where('code', 'EXPRTA1')->first();

        if (!$exportArtefact) {
            $exportArtefact = StoreArtefact::make()->action($this->production, ['code' => 'EXPRTA1', 'name' => 'Export artefact']);
        }
        $exportArtefact->manufactureTasks()->sync([
            $exportTask->id => ['position' => 1, 'units_per_artefact' => 1],
        ]);
        $exportJobOrder     = StoreJobOrder::make()->action($this->production, []);
        $exportJobOrderItem = StoreJobOrderItem::make()->action($exportJobOrder, [
            'artefact_id' => $exportArtefact->id,
            'quantity'    => 1000,
        ]);
        ConfirmJobOrder::make()->action($exportJobOrder);
        $exportJobOrderItemTask = $exportJobOrderItem->tasks()->first();

        $session = makePayBandSession($exportJobOrderItemTask, 900, 4.9333);
        $session = \App\Actions\Production\ManufactureTaskSession\CalculateManufactureTaskSessionPay::run($session);

        $export = new \App\Exports\Production\ManufacturePayrollExport(
            $this->production,
            $session->ended_at->clone()->subDay(),
            $session->ended_at->clone()->addDay()
        );

        $rows = collect($export->array());
        $row  = $rows->first(fn ($row) => $row[1] === 'EXPRT1');

        expect($row)->not->toBeNull()
            ->and($row[11])->toBe('production')
            ->and((float) $row[16])->toBe((float) $session->hourly_rate)
            ->and((float) $row[17])->toBe((float) $session->pay)
            ->and((float) $row[18])->toBe((float) $session->bonus);
    });

    test('below every target lands in band 0', function () {
        $this->payBandJobOrderItemTask->manufactureTask->update(['standard_rate' => 216]);
        $session = makePayBandSession($this->payBandJobOrderItemTask, 380, 2);

        $session = \App\Actions\Production\ManufactureTaskSession\CalculateManufactureTaskSessionPay::run($session);

        expect($session->band_code)->toBe('0')
            ->and((float) $session->pay)->toBe(25.42)
            ->and((float) $session->bonus)->toBe(0.0);
    });

    test('units per hour exactly on a band target lands on that band', function () {
        $this->payBandJobOrderItemTask->manufactureTask->update(['standard_rate' => 10000]);

        $sessionAtBand1 = makePayBandSession($this->payBandJobOrderItemTask, 10228, 1);
        $sessionAtBand1 = \App\Actions\Production\ManufactureTaskSession\CalculateManufactureTaskSessionPay::run($sessionAtBand1);
        expect($sessionAtBand1->band_code)->toBe('1');

        $sessionAtBand3 = makePayBandSession($this->payBandJobOrderItemTask, 11802, 1);
        $sessionAtBand3 = \App\Actions\Production\ManufactureTaskSession\CalculateManufactureTaskSessionPay::run($sessionAtBand3);
        expect($sessionAtBand3->band_code)->toBe('3');
    });

    test('a non production activity always pays band 0 with no units per hour', function () {
        $this->payBandJobOrderItemTask->manufactureTask->update(['standard_rate' => 152]);
        $session = makePayBandSession(
            $this->payBandJobOrderItemTask,
            900,
            4.9333,
            0,
            \App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionActivityTypeEnum::CLEANING->value
        );

        $session = \App\Actions\Production\ManufactureTaskSession\CalculateManufactureTaskSessionPay::run($session);

        expect($session->band_code)->toBe('0')
            ->and($session->units_per_hour)->toBeNull();
    });

    test('manufacture floor shows live band feedback when the task has a standard rate', function () {
        $this->payBandJobOrderItemTask->manufactureTask->update(['standard_rate' => 152]);

        \App\Actions\Production\ManufactureTaskSession\StartManufactureTaskSession::make()->action(
            auth()->user(),
            $this->payBandJobOrderItemTask
        );

        $response = get(route('grp.org.productions.show.floor', [
            $this->organisation->slug,
            $this->production->slug,
        ]));

        $response->assertInertia(function (AssertableInertia $page) {
            $page->component('Org/Production/ManufactureFloor')
                ->has('open_session.band_feedback.bands', 3)
                ->where('open_session.band_feedback.band0_hourly_rate', 12.71)
                ->where('open_session.band_feedback.bands.0.target_units_per_hour', round(152 * 1.0228, 1))
                ->where('open_session.band_feedback.bands.1.target_units_per_hour', round(152 * 1.1015, 1))
                ->where('open_session.band_feedback.bands.2.target_units_per_hour', round(152 * 1.1802, 1));
        });

        $this->payBandJobOrderItemTask->manufactureTask->update(['standard_rate' => null]);
        \App\Models\Production\ManufactureTaskSession::where('user_id', auth()->user()->id)
            ->where('state', \App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum::OPEN)
            ->update(['job_order_item_task_id' => $this->payBandJobOrderItemTask->id]);

        $response = get(route('grp.org.productions.show.floor', [
            $this->organisation->slug,
            $this->production->slug,
        ]));

        $response->assertInertia(function (AssertableInertia $page) {
            $page->component('Org/Production/ManufactureFloor')
                ->where('open_session.band_feedback', null);
        });

        \App\Models\Production\ManufactureTaskSession::where('user_id', auth()->user()->id)
            ->where('state', \App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum::OPEN)
            ->update(['state' => \App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum::CLOSED]);
    });

    test('development activity pays the development band', function () {
        $session = makePayBandSession(
            $this->payBandJobOrderItemTask,
            0,
            2,
            0,
            \App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionActivityTypeEnum::DEVELOPMENT->value
        );

        $session = \App\Actions\Production\ManufactureTaskSession\CalculateManufactureTaskSessionPay::run($session);

        expect($session->band_code)->toBe('D')
            ->and((float) $session->hourly_rate)->toBe(13.30);
    });

    test('a production without any configured bands keeps the pay fields empty', function () {
        $unbandedProduction = \App\Actions\Production\Production\StoreProduction::make()->action(
            $this->organisation,
            ['code' => 'NOBAND1', 'name' => 'No band production']
        );
        $unbandedTask = \App\Actions\Production\ManufactureTask\StoreManufactureTask::make()->action(
            $unbandedProduction,
            [
                'code'                             => 'NBT1',
                'name'                             => 'No band task',
                'task_materials_cost'               => 1,
                'task_energy_cost'                  => 1,
                'task_other_cost'                   => 1,
                'task_work_cost'                    => 1,
                'task_lower_target'                 => 1,
                'task_upper_target'                 => 1,
                'operative_reward_terms'            => ManufactureTaskOperativeRewardTermsEnum::ABOVE_LOWER_LIMIT,
                'operative_reward_allowance_type'   => ManufactureTaskOperativeRewardAllowanceTypeEnum::OFFSET_SALARY,
                'operative_reward_amount'           => 1,
            ]
        );
        $unbandedTask->update(['standard_rate' => 152]);
        $unbandedArtefact = StoreArtefact::make()->action($unbandedProduction, ['code' => 'NBA1', 'name' => 'No band artefact']);
        $unbandedArtefact->manufactureTasks()->sync([
            $unbandedTask->id => ['position' => 1, 'units_per_artefact' => 1],
        ]);
        $jobOrder     = StoreJobOrder::make()->action($unbandedProduction, []);
        $jobOrderItem = StoreJobOrderItem::make()->action($jobOrder, [
            'artefact_id' => $unbandedArtefact->id,
            'quantity'    => 1000,
        ]);
        ConfirmJobOrder::make()->action($jobOrder);
        $jobOrderItemTask = $jobOrderItem->tasks()->first();

        $session = makePayBandSession($jobOrderItemTask, 900, 4.9333);
        $session = \App\Actions\Production\ManufactureTaskSession\CalculateManufactureTaskSessionPay::run($session);

        expect($session->band_code)->toBeNull()
            ->and($session->pay)->toBeNull()
            ->and($session->bonus)->toBeNull();
    });

    test('closing a non production session without a reason is rejected', function () {
        $session = makePayBandSession($this->payBandJobOrderItemTask, 0, 2);
        $session->update([
            'state'         => \App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum::OPEN,
            'activity_type' => \App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionActivityTypeEnum::PRODUCTION,
            'started_at'    => now()->subHours(2),
        ]);

        expect(
            fn () => \App\Actions\Production\ManufactureTaskSession\CloseManufactureTaskSession::make()->action($session, [
                'quantity_made' => 0,
                'activity_type' => \App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionActivityTypeEnum::MAINTENANCE->value,
            ])
        )->toThrow(\Illuminate\Validation\ValidationException::class);

        $closed = \App\Actions\Production\ManufactureTaskSession\CloseManufactureTaskSession::make()->action($session, [
            'quantity_made'         => 0,
            'activity_type'         => \App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionActivityTypeEnum::MAINTENANCE->value,
            'non_productive_reason' => 'End of shift line clean',
        ]);

        expect($closed->band_code)->toBe('0')
            ->and((float) $closed->hourly_rate)->toBe(12.71);
    });

    test('band rates stay cost neutral against the standard rate within two percent', function () {
        $bands = \App\Models\Production\ManufacturePayBand::where('production_id', $this->production->id)
            ->whereIn('code', ['1', '2', '3'])
            ->get();

        $standardRate = 152;
        $ratios       = $bands->map(fn ($band) => (float) $band->hourly_rate / ($standardRate * (float) $band->target_multiplier));

        $reference = $ratios->first();
        foreach ($ratios as $ratio) {
            expect(abs($ratio - $reference) / $reference)->toBeLessThan(0.02);
        }
    });

    test('a recipe step target overrides the task target, sets the pay band and is snapshotted when the session closes', function () {
        $this->manufactureTask->update(['standard_rate' => 10000]);

        SetArtefactsRecipe::make()->action($this->production, [
            'artefacts' => [$this->artefact->id],
            'steps'     => [[
                'manufacture_task_id' => $this->manufactureTask->id,
                'position'            => 1,
                'units_per_artefact'  => 1,
                'standard_rate'       => 152,
            ]],
        ]);

        $session = makePayBandSession($this->payBandJobOrderItemTask, 900, 4.9333);
        expect($session->recipeStandardRate())->toBe(152.0);

        $session = \App\Actions\Production\ManufactureTaskSession\CalculateManufactureTaskSessionPay::run($session);
        expect($session->band_code)->toBe('3');

        $openSession = makePayBandSession($this->payBandJobOrderItemTask, 0, 1);
        $openSession->update(['state' => \App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum::OPEN, 'started_at' => now()->subHour(), 'ended_at' => null]);

        $closed = CloseManufactureTaskSession::make()->action($openSession, ['quantity_made' => 200])->refresh();

        expect((float) $closed->standard_rate)->toBe(152.0)
            ->and($closed->is_under_target)->toBeFalse();
    });

    test('a step given its first target repays this week\'s sessions made without one and leaves earlier weeks alone', function () {
        $this->manufactureTask->update(['standard_rate' => null]);
        $recipe = fn (?float $standardRate) => SetArtefactsRecipe::make()->action($this->production, [
            'artefacts' => [$this->artefact->id],
            'steps'     => [[
                'manufacture_task_id' => $this->manufactureTask->id,
                'position'            => 1,
                'units_per_artefact'  => 1,
                'standard_rate'       => $standardRate,
            ]],
        ]);
        $recipe(null);

        $thisWeek = \App\Actions\Production\ManufactureTaskSession\CalculateManufactureTaskSessionPay::run(makePayBandSession($this->payBandJobOrderItemTask, 900, 4.9333));
        $lastWeek = makePayBandSession($this->payBandJobOrderItemTask, 900, 4.9333);
        $lastWeek->update(['started_at' => now()->startOfWeek()->subDays(3), 'ended_at' => now()->startOfWeek()->subDays(3)->addSeconds(17760)]);
        $lastWeek = \App\Actions\Production\ManufactureTaskSession\CalculateManufactureTaskSessionPay::run($lastWeek);

        expect($thisWeek->band_code)->toBe('0')
            ->and($lastWeek->band_code)->toBe('0');

        $recipe(152);

        $thisWeek->refresh();
        $lastWeek->refresh();

        expect((float) $thisWeek->standard_rate)->toBe(152.0)
            ->and($thisWeek->band_code)->toBe('3')
            ->and((float) $thisWeek->pay)->toBe(74.0)
            ->and((float) $thisWeek->bonus)->toBe(11.3)
            ->and($thisWeek->is_under_target)->toBeFalse()
            ->and($lastWeek->standard_rate)->toBeNull()
            ->and($lastWeek->band_code)->toBe('0')
            ->and((float) $lastWeek->bonus)->toBe(0.0);
    });

    test('a short session well above a low step target is not flagged', function () {
        AttachManufactureTaskToArtefact::make()->action($this->artefact, [
            'manufacture_task_id' => $this->manufactureTask->id,
            'position'            => 1,
            'units_per_artefact'  => 1,
            'standard_rate'       => 1,
        ]);

        $openSession = makePayBandSession($this->payBandJobOrderItemTask, 0, 1);
        $openSession->update(['state' => \App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum::OPEN, 'started_at' => now()->subMinute(), 'ended_at' => null, 'break_minutes' => 0]);

        $closed = CloseManufactureTaskSession::make()->action($openSession, ['quantity_made' => 1])->refresh();

        expect((float) $closed->standard_rate)->toBe(1.0)
            ->and($closed->paidHours())->toBeLessThan(0.1)
            ->and($closed->is_under_target)->toBeFalse();
    });

    test('a session closed below its step target is flagged and a manager logs the reason', function () {
        AttachManufactureTaskToArtefact::make()->action($this->artefact, [
            'manufacture_task_id' => $this->manufactureTask->id,
            'position'            => 1,
            'units_per_artefact'  => 1,
            'standard_rate'       => 152,
        ]);

        $openSession = makePayBandSession($this->payBandJobOrderItemTask, 0, 1);
        $openSession->update(['state' => \App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum::OPEN, 'started_at' => now()->subHour(), 'ended_at' => null]);

        $closed = CloseManufactureTaskSession::make()->action($openSession, ['quantity_made' => 40])->refresh();

        expect($closed->is_under_target)->toBeTrue()
            ->and($closed->band_code)->toBe('0');

        $reviewed = \App\Actions\Production\ManufactureTaskSession\ReviewUnderTargetManufactureTaskSession::make()->action($closed, auth()->user(), [
            'under_target_reason' => 'machine_breakdown',
            'under_target_note'   => 'Shrink tunnel down 30 minutes',
        ]);

        expect($reviewed->under_target_reason)->toBe(\App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionUnderTargetReasonEnum::MACHINE_BREAKDOWN)
            ->and($reviewed->under_target_note)->toBe('Shrink tunnel down 30 minutes')
            ->and($reviewed->under_target_reviewed_by)->toBe(auth()->user()->id)
            ->and($reviewed->under_target_reviewed_at)->not->toBeNull();

        $onTarget = makePayBandSession($this->payBandJobOrderItemTask, 200, 1);

        expect(fn () => \App\Actions\Production\ManufactureTaskSession\ReviewUnderTargetManufactureTaskSession::make()->action($onTarget, auth()->user(), [
            'under_target_reason' => 'other',
        ]))->toThrow(\Illuminate\Validation\ValidationException::class);
    });

    test('an hourly artisan gets no tier, bonus or under-target flag, and their sessions this week follow the pay type', function () {
        AttachManufactureTaskToArtefact::make()->action($this->artefact, [
            'manufacture_task_id' => $this->manufactureTask->id,
            'position'            => 1,
            'units_per_artefact'  => 1,
            'standard_rate'       => 152,
        ]);
        $this->manufactureTask->update(['operative_reward_terms' => 'above_lower_limit']);

        $modelData                    = Employee::factory()->make(['organisation_id' => $this->organisation->id])->toArray();
        $modelData['worker_number']   = 'W'.rand(1000, 9999);
        $modelData['alias']           = 'Alias '.rand(1000, 9999);
        $modelData['type']            = \App\Enums\HumanResources\Employee\EmployeeTypeEnum::EMPLOYEE;
        $modelData['employment_type'] = \App\Enums\HumanResources\Employee\EmploymentTypeEnum::FULL_TIME;
        $modelData['state']           = \App\Enums\HumanResources\Employee\EmployeeStateEnum::WORKING;
        $artisan                      = StoreEmployee::make()->action($this->organisation, $modelData);

        $closeOpen = function (float $quantityMade) use ($artisan) {
            $session = makePayBandSession($this->payBandJobOrderItemTask, 0, 1);
            $session->update(['state' => \App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum::OPEN, 'employee_id' => $artisan->id, 'started_at' => now()->subHour(), 'ended_at' => null]);

            return CloseManufactureTaskSession::make()->action($session, ['quantity_made' => $quantityMade])->refresh();
        };

        $pieceRate = $closeOpen(40);
        expect($artisan->artisan_pay_type)->toBe(\App\Enums\Production\Artisan\ArtisanPayTypeEnum::PIECE_RATE)
            ->and($pieceRate->is_under_target)->toBeTrue()
            ->and($pieceRate->operative_reward_terms)->toBe('above_lower_limit');

        \App\Actions\Production\Artisan\SetArtisanPayType::make()->action($this->production, $artisan, ['artisan_pay_type' => 'hourly']);

        $pieceRate->refresh();
        expect($artisan->refresh()->artisan_pay_type)->toBe(\App\Enums\Production\Artisan\ArtisanPayTypeEnum::HOURLY)
            ->and($pieceRate->artisan_pay_type)->toBe(\App\Enums\Production\Artisan\ArtisanPayTypeEnum::HOURLY)
            ->and($pieceRate->is_under_target)->toBeFalse()
            ->and($pieceRate->operative_reward_terms)->toBe('never')
            ->and((float) $pieceRate->task_work_cost)->toBe(0.0);

        $fast = $closeOpen(900);
        expect($fast->artisan_pay_type)->toBe(\App\Enums\Production\Artisan\ArtisanPayTypeEnum::HOURLY)
            ->and($fast->band_code)->toBe('0')
            ->and((float) $fast->bonus)->toBe(0.0)
            ->and($fast->is_under_target)->toBeFalse()
            ->and((float) $fast->standard_rate)->toBe(152.0)
            ->and((float) $fast->units_per_hour)->toBeGreaterThan(152.0)
            ->and($fast->operative_reward_terms)->toBe('never');

        $development = makePayBandSession($this->payBandJobOrderItemTask, 0, 2, 0, \App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionActivityTypeEnum::DEVELOPMENT->value);
        $development->update(['artisan_pay_type' => \App\Enums\Production\Artisan\ArtisanPayTypeEnum::HOURLY]);
        $development = \App\Actions\Production\ManufactureTaskSession\CalculateManufactureTaskSessionPay::run($development);
        expect($development->band_code)->toBe('0')
            ->and((float) $development->bonus)->toBe(0.0);

        $artisans = collect(get(route('grp.org.productions.show.artisans.index', [$this->organisation->slug, $this->production->slug, 'artisan_pay_type' => 'hourly']))
            ->viewData('page')['props']['artisans']);
        $sessions = $artisans->pluck('jobs')->flatten(1)->pluck('steps')->flatten(1)->pluck('sessions')->flatten(1);
        expect($sessions->pluck('id'))->toContain($fast->id)
            ->and($sessions->every(fn (array $session) => $session['is_hourly']))->toBeTrue();
    });

    test('job lines that share a step are made as one combined batch and the quantity and time are shared back to each line', function () {
        $suffix    = rand(10000, 99999);
        $artefacts = collect(['CMB-A', 'CMB-B'])->map(function (string $code) use ($suffix) {
            $artefact = StoreArtefact::make()->action($this->production, ['code' => $code.$suffix, 'name' => 'Combined '.$code]);
            $artefact->manufactureTasks()->sync([]);
            AttachManufactureTaskToArtefact::make()->action($artefact, [
                'manufacture_task_id' => $this->manufactureTask->id,
                'position'            => 1,
                'units_per_artefact'  => 1,
                'standard_rate'       => 21,
            ]);

            return $artefact->refresh();
        });

        $tasks = $artefacts->map(function (Artefact $artefact) {
            $jobOrder = StoreJobOrder::make()->action($this->production, []);
            $item     = StoreJobOrderItem::make()->action($jobOrder, ['artefact_id' => $artefact->id, 'quantity' => 100]);
            ConfirmJobOrder::make()->action($jobOrder);

            return $item->tasks()->first();
        });
        [$taskA, $taskB] = $tasks->all();

        $combineUrl = route('grp.models.production.combined_task.store', ['production' => $this->production->id]);
        \Pest\Laravel\post($combineUrl, ['job_order_item_task_ids' => [$taskA->id]])->assertSessionHasErrors('job_order_item_task_ids');
        \Pest\Laravel\post($combineUrl, ['job_order_item_task_ids' => [$taskA->id, $taskB->id]])->assertSessionHasNoErrors();
        \Pest\Laravel\post($combineUrl, ['job_order_item_task_ids' => [$taskA->id, $taskB->id]])->assertSessionHasErrors('job_order_item_task_ids');

        expect($taskA->refresh()->combined_task_id)->toBe($taskA->id)
            ->and($taskB->refresh()->combined_task_id)->toBe($taskA->id);

        $floorTasks = collect(get(route('grp.org.productions.show.floor', [$this->organisation->slug, $this->production->slug]))
            ->viewData('page')['props']['tasks'])->whereIn('id', [$taskA->id, $taskB->id]);
        expect($floorTasks)->toHaveCount(1)
            ->and($floorTasks->first()['quantity_required'])->toBe(200.0)
            ->and(collect($floorTasks->first()['combined'])->pluck('id')->all())->toBe([$taskA->id, $taskB->id])
            ->and($floorTasks->first()['separate_route'])->not->toBeNull();

        $session = StartManufactureTaskSession::make()->action($this->guest->getUser(), $taskB);
        expect($session->job_order_item_task_id)->toBe($taskA->id)
            ->and($session->is_combined)->toBeTrue()
            ->and($taskB->refresh()->state)->toBe(JobOrderItemTaskStateEnum::IN_PROGRESS);

        expect(fn () => \App\Actions\Production\JobOrderItemTask\SeparateJobOrderItemTasks::make()->action($taskA))
            ->toThrow(\Illuminate\Validation\ValidationException::class);

        $session->update(['started_at' => now()->subHours(8)]);
        $closed = CloseManufactureTaskSession::make()->action($session, ['quantity_made' => 200])->refresh();

        expect((float) $closed->quantity_made)->toBe(200.0)
            ->and((float) $closed->standard_rate)->toBe(21.0)
            ->and($closed->is_under_target)->toBeFalse()
            ->and($closed->band_code)->toBe('3')
            ->and((float) $closed->pay)->toBe(120.0)
            ->and($closed->shares->pluck('quantity_made', 'job_order_item_task_id')->map(fn ($quantity) => (float) $quantity)->all())->toBe([$taskA->id => 100.0, $taskB->id => 100.0])
            ->and((float) $taskA->refresh()->quantity_made)->toBe(100.0)
            ->and((float) $taskB->refresh()->quantity_made)->toBe(100.0)
            ->and($taskA->state)->toBe(JobOrderItemTaskStateEnum::DONE)
            ->and($taskB->state)->toBe(JobOrderItemTaskStateEnum::DONE);

        $item = collect(get(route('grp.org.productions.show.operations.job-orders.show', [$this->organisation->slug, $this->production->slug, $taskB->jobOrder->slug]))
            ->viewData('page')['props']['items'])->first();
        expect($item['tasks'][0]['combined_with'])->toBe($artefacts[0]->code.' ('.$taskA->jobOrder->reference.')');

        \App\Actions\Production\ManufactureTaskSession\VoidManufactureTaskSession::make()->action($closed);
        expect((float) $taskA->refresh()->quantity_made)->toBe(0.0)
            ->and((float) $taskB->refresh()->quantity_made)->toBe(0.0);

        \App\Actions\Production\JobOrderItemTask\SeparateJobOrderItemTasks::make()->action($taskA);
        expect($taskA->refresh()->combined_task_id)->toBeNull()
            ->and($taskB->refresh()->combined_task_id)->toBeNull();

        expect(CloseManufactureTaskSession::apportion(201, collect([$taskA->id => 0.5, $taskB->id => 0.5])))->toBe([$taskA->id => 101.0, $taskB->id => 100.0])
            ->and(CloseManufactureTaskSession::apportion(10.5, collect([$taskA->id => 0.5, $taskB->id => 0.5])))->toBe([$taskA->id => 5.25, $taskB->id => 5.25]);
    });

    test('changing a recipe step position keeps its target', function () {
        AttachManufactureTaskToArtefact::make()->action($this->artefact, [
            'manufacture_task_id' => $this->manufactureTask->id,
            'position'            => 1,
            'standard_rate'       => 152,
        ]);
        AttachManufactureTaskToArtefact::make()->action($this->artefact, [
            'manufacture_task_id' => $this->manufactureTask->id,
            'position'            => 2,
        ]);

        expect((float) $this->artefact->manufactureTasks()->first()->pivot->standard_rate)->toBe(152.0);
    });
});

describe('reward sheet import', function () {
    beforeEach(function () {
        $this->rewardTask = \App\Models\Production\ManufactureTask::where('production_id', $this->production->id)
            ->where('code', 'RWT1')
            ->first();

        if (!$this->rewardTask) {
            $this->rewardTask = \App\Actions\Production\ManufactureTask\StoreManufactureTask::make()->action(
                $this->production,
                [
                    'code'                             => 'RWT1',
                    'name'                             => 'Reward task',
                    'task_materials_cost'               => 1,
                    'task_energy_cost'                  => 1,
                    'task_other_cost'                   => 1,
                    'task_work_cost'                    => 1,
                    'task_lower_target'                 => 1,
                    'task_upper_target'                 => 1,
                    'operative_reward_terms'            => ManufactureTaskOperativeRewardTermsEnum::ABOVE_LOWER_LIMIT,
                    'operative_reward_allowance_type'   => ManufactureTaskOperativeRewardAllowanceTypeEnum::OFFSET_SALARY,
                    'operative_reward_amount'           => 1,
                ]
            );
        }

        $this->rewardTask->update(['standard_rate' => null, 'target_override_reason' => null]);

        $this->payBandCountBeforeImport = \App\Models\Production\ManufacturePayBand::where('production_id', $this->production->id)->count();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Product Families');

        $sheet->fromArray(['Family', 'Description', 'Piece Rate (Old)', '0', '1', '2', '3'], null, 'A2');
        $sheet->fromArray(['RWT1', 'Matches the reward task', 0.1, 130, 133, 143, 153], null, 'A3');
        $sheet->fromArray(['ORPHAN1', 'No matching task', 0.2, 65, 66, 71, 76], null, 'A4');
        $sheet->fromArray(['ZEROED', 'Zero piece rate is skipped', 0, 0, 0, 0, 0], null, 'A5');
        $sheet->fromArray(['RWT1', 'Hand typed target 0 override', 0.05, 100, 267, 287, 307], null, 'A6');

        $this->rewardSheetPath = tempnam(sys_get_temp_dir(), 'reward-sheet').'.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($this->rewardSheetPath);
    });

    afterEach(function () {
        if (isset($this->rewardSheetPath) && file_exists($this->rewardSheetPath)) {
            unlink($this->rewardSheetPath);
        }
    });

    test('dry run changes nothing', function () {
        $this->artisan('manufacture:import-reward-sheet', [
            'production' => $this->production->slug,
            'file'       => $this->rewardSheetPath,
            '--dry-run'  => true,
        ])->assertSuccessful();

        expect($this->rewardTask->refresh()->standard_rate)->toBeNull()
            ->and(\App\Models\Production\ManufacturePayBand::where('production_id', $this->production->id)->count())->toBe($this->payBandCountBeforeImport);
    });

    test('real run sets standard rate, seeds bands, flags override and reports the orphan', function () {
        $this->artisan('manufacture:import-reward-sheet', [
            'production' => $this->production->slug,
            'file'       => $this->rewardSheetPath,
        ])
            ->assertSuccessful()
            ->expectsOutputToContain('ORPHAN1');

        expect((float) $this->rewardTask->refresh()->standard_rate)->toBe(round(13.00 / 0.05, 4))
            ->and($this->rewardTask->target_override_reason)->not->toBeNull()
            ->and(\App\Models\Production\ManufacturePayBand::where('production_id', $this->production->id)->where('effective_from', now()->startOfYear())->count())->toBe(6);
    });

    test('without a file only the pay bands are seeded, with the sheet target multipliers', function () {
        \App\Models\Production\ManufacturePayBand::where('production_id', $this->production->id)->where('effective_from', now()->startOfYear())->delete();

        $this->artisan('manufacture:import-reward-sheet', [
            'production' => $this->production->slug,
        ])->assertSuccessful();

        $multipliers = \App\Models\Production\ManufacturePayBand::where('production_id', $this->production->id)
            ->where('effective_from', now()->startOfYear())
            ->pluck('target_multiplier', 'code')
            ->map(fn ($multiplier) => $multiplier === null ? null : (float) $multiplier)
            ->all();

        expect($this->rewardTask->refresh()->standard_rate)->toBeNull()
            ->and($multipliers)->toEqual(['0' => 1.0, '1' => 1.025, '2' => 1.105, '3' => 1.185, 'D' => null, 'DG' => null]);
    });

    test('create-missing with dry-run creates nothing', function () {
        \App\Models\Production\ManufactureTask::where('production_id', $this->production->id)->where('code', 'ORPHAN1')->forceDelete();

        $this->artisan('manufacture:import-reward-sheet', [
            'production'        => $this->production->slug,
            'file'              => $this->rewardSheetPath,
            '--create-missing'  => true,
            '--dry-run'         => true,
        ])->assertSuccessful();

        expect(\App\Models\Production\ManufactureTask::where('production_id', $this->production->id)->where('code', 'ORPHAN1')->exists())->toBeFalse();
    });

    test('create-missing creates the orphan task', function () {
        \App\Models\Production\ManufactureTask::where('production_id', $this->production->id)->where('code', 'ORPHAN1')->forceDelete();

        $this->artisan('manufacture:import-reward-sheet', [
            'production'        => $this->production->slug,
            'file'              => $this->rewardSheetPath,
            '--create-missing'  => true,
        ])->assertSuccessful();

        $orphanTask = \App\Models\Production\ManufactureTask::where('production_id', $this->production->id)
            ->where('code', 'ORPHAN1')
            ->first();

        expect($orphanTask)->not->toBeNull()
            ->and((float) $orphanTask->standard_rate)->toBe(round(13.00 / 0.2, 4))
            ->and($orphanTask->slug)->not->toBeNull();
    });

    test('create-missing is idempotent on re-run', function () {
        \App\Models\Production\ManufactureTask::where('production_id', $this->production->id)->where('code', 'ORPHAN1')->forceDelete();

        $this->artisan('manufacture:import-reward-sheet', [
            'production'        => $this->production->slug,
            'file'              => $this->rewardSheetPath,
            '--create-missing'  => true,
        ])->assertSuccessful();

        expect(\App\Models\Production\ManufactureTask::where('production_id', $this->production->id)->where('code', 'ORPHAN1')->count())->toBe(1);

        $this->artisan('manufacture:import-reward-sheet', [
            'production'        => $this->production->slug,
            'file'              => $this->rewardSheetPath,
            '--create-missing'  => true,
        ])->assertSuccessful();

        expect(\App\Models\Production\ManufactureTask::where('production_id', $this->production->id)->where('code', 'ORPHAN1')->count())->toBe(1);
    });
});

test('artisans can be attached and detached from a family and an artefact, first one is primary', function () {
    $family   = StoreArtefactDepartment::make()->action($this->production, ['code' => 'ARTS', 'name' => 'Artisan family']);
    $artefact = StoreArtefact::make()->action($this->production, ['code' => 'ARTS-1', 'name' => 'Artisan artefact', 'artefact_department_id' => $family->id]);

    $employees = collect(range(1, 2))->map(function () {
        $modelData = Employee::factory()->make(['organisation_id' => $this->organisation->id])->toArray();
        $modelData['worker_number']   = 'W'.rand(1000, 9999);
        $modelData['alias']           = 'Alias '.rand(1000, 9999);
        $modelData['type']            = \App\Enums\HumanResources\Employee\EmployeeTypeEnum::EMPLOYEE;
        $modelData['employment_type'] = \App\Enums\HumanResources\Employee\EmploymentTypeEnum::FULL_TIME;
        $modelData['state']           = \App\Enums\HumanResources\Employee\EmployeeStateEnum::WORKING;

        return StoreEmployee::make()->action($this->organisation, $modelData);
    });

    AttachArtisan::make()->action($family, ['employee_id' => $employees[0]->id]);
    AttachArtisan::make()->action($family, ['employee_id' => $employees[1]->id]);
    AttachArtisan::make()->action($family, ['employee_id' => $employees[1]->id]);
    expect($family->artisans()->pluck('employees.id')->all())->toBe([$employees[0]->id, $employees[1]->id]);

    DetachArtisan::make()->action($family, $employees[0]);
    expect($family->artisans()->pluck('employees.id')->all())->toBe([$employees[1]->id]);

    AttachArtisan::make()->action($artefact, ['employee_id' => $employees[0]->id]);
    expect($artefact->artisans()->pluck('employees.id')->all())->toBe([$employees[0]->id]);
});

test('an employee can be hidden from and restored to the artisan roster', function () {
    $modelData = Employee::factory()->make(['organisation_id' => $this->organisation->id])->toArray();
    $modelData['worker_number']   = 'W'.rand(1000, 9999);
    $modelData['alias']           = 'Alias '.rand(1000, 9999);
    $modelData['type']            = \App\Enums\HumanResources\Employee\EmployeeTypeEnum::EMPLOYEE;
    $modelData['employment_type'] = \App\Enums\HumanResources\Employee\EmploymentTypeEnum::FULL_TIME;
    $modelData['state']           = \App\Enums\HumanResources\Employee\EmployeeStateEnum::WORKING;
    $employee = StoreEmployee::make()->action($this->organisation, $modelData);

    $production = ToggleArtisanInRoster::make()->action($this->production, $employee, true);
    expect($production->data['hidden_artisan_ids'])->toBe([$employee->id]);

    $production = ToggleArtisanInRoster::make()->action($production, $employee, true);
    expect($production->data['hidden_artisan_ids'])->toBe([$employee->id]);

    $production = ToggleArtisanInRoster::make()->action($production, $employee, false);
    expect($production->data['hidden_artisan_ids'])->toBe([]);
});

test('a task that is not piece rate snapshots a zero rate when its session closes', function () {
    $this->manufactureTask->update(['is_piece_rate' => false]);
    $jobOrder = StoreJobOrder::make()->action($this->production, []);
    $this->artefact->manufactureTasks()->sync([
        $this->manufactureTask->id => ['position' => 1, 'units_per_artefact' => 1],
    ]);
    $item = StoreJobOrderItem::make()->action($jobOrder, ['artefact_id' => $this->artefact->id, 'quantity' => 4]);
    ConfirmJobOrder::make()->action($jobOrder);
    $task = $item->tasks()->first();
    $user = createAdminGuest($this->group)->getUser();

    $session = StartManufactureTaskSession::make()->action($user, $task);
    $session = CloseManufactureTaskSession::make()->action($session, ['quantity_made' => 4]);

    expect((float) $session->task_work_cost)->toBe(0.0)
        ->and((float) $session->operative_reward_amount)->toBe(0.0)
        ->and((float) $session->quantity_made)->toBe(4.0);

    $this->manufactureTask->update(['is_piece_rate' => true]);
});
