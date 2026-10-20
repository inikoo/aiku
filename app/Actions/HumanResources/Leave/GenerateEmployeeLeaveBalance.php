<?php

/*
 * @Author: andiferdiawan (https://github.com/andiferdiawan)
 * @Created: 2026-06-12
 * * @Copyright: Copyright (c) 2026, andiferdiawan
 */

namespace App\Actions\HumanResources\Leave;

use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Models\HumanResources\EmployeeContract;
use App\Models\HumanResources\EmployeeLeaveBalance;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateEmployeeLeaveBalance
{
    use AsAction;

    public function handle(EmployeeContract $contract, ?Carbon $periodStart = null): EmployeeLeaveBalance
    {
        $start = $periodStart ?? $contract->start_date;
        $end   = $this->periodEnd($contract, $start);

        $balance = EmployeeLeaveBalance::firstOrCreate(
            [
                'employee_contract_id' => $contract->id,
                'period_start'         => $start->toDateString(),
            ],
            [
                'employee_id'  => $contract->employee_id,
                'period_end'   => $end?->toDateString(),
                'annual_used'  => 0,
                'medical_used' => 0,
                'unpaid_used'  => 0,
            ]
        );

        if ($balance->wasRecentlyCreated) {
            $this->moveRecordedLeavesInto($balance);
        }

        return $balance;
    }

    private function moveRecordedLeavesInto(EmployeeLeaveBalance $newBalance): void
    {
        $balances = EmployeeLeaveBalance::query()->where('employee_id', $newBalance->employee_id)->orderBy('id')->get();
        $leaves   = RecalculateEmployeeLeaveBalance::approvedLeaves($newBalance->employee_id);
        $layout   = $balances->map(fn (EmployeeLeaveBalance $balance): array => RecalculateEmployeeLeaveBalance::layoutRow($balance));

        $recordedBefore = RecalculateEmployeeLeaveBalance::recordedByOwner($leaves, $layout->reject(fn (array $row): bool => $row['key'] === (string) $newBalance->id));
        $recordedAfter  = RecalculateEmployeeLeaveBalance::recordedByOwner($leaves, $layout);

        foreach ($balances as $balance) {
            if ($balance->id === $newBalance->id) {
                RecalculateEmployeeLeaveBalance::run($newBalance);

                continue;
            }

            $before = $recordedBefore[(string) $balance->id] ?? null;
            $after  = $recordedAfter[(string) $balance->id] ?? null;

            if ($before === null || $after === null || $before === $after) {
                continue;
            }

            $used = [];
            foreach (RecalculateEmployeeLeaveBalance::BUCKET_COLUMNS as $column) {
                $used[$column] = round(max($after[$column], (float) $balance->getAttribute($column) - $before[$column] + $after[$column]), 2);
            }

            $balance->update($used);
        }
    }

    public function periodStartFor(EmployeeContract $contract, CarbonInterface $date): Carbon
    {
        $start = Carbon::parse($contract->start_date->toDateString());

        while ($start->copy()->addYear()->subDay()->lt($date)) {
            $start->addYear();
        }

        return $start;
    }

    public function periodStartOfYear(EmployeeContract $contract, int $yearIndex): Carbon
    {
        return Carbon::parse($contract->start_date->toDateString())->addYears($yearIndex);
    }

    public function periodEnd(EmployeeContract $contract, Carbon $start): ?Carbon
    {
        if ($contract->end_date === null) {
            return $start->copy()->addYear()->subDay();
        }

        $oneYearOut = $start->copy()->addYear()->subDay();

        return $contract->end_date->lt($oneYearOut) ? $contract->end_date : $oneYearOut;
    }

    public function authorize(ActionRequest $request): bool
    {
        $contract       = $request->route('contract');
        $organisationId = $contract instanceof EmployeeContract ? $contract->employee?->organisation_id : null;

        if (!$organisationId || !$request->user()) {
            return false;
        }

        return $request->user()->authTo(["human-resources.$organisationId.edit", "org-supervisor.$organisationId.human-resources"]);
    }

    public function asController(EmployeeContract $contract): EmployeeLeaveBalance
    {
        return $this->handle($contract);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back()->with('notification', [
            'status'      => 'success',
            'title'       => __('Success!'),
            'description' => __('Leave balance generated successfully.'),
        ]);
    }

    public function jsonResponse(EmployeeLeaveBalance $balance): JsonResponse
    {
        $contract = $balance->contract;

        return new JsonResponse([
            'created' => $balance->wasRecentlyCreated,
            'balance' => [
                'id'           => $balance->id,
                'annual_days'  => $contract?->annual_leave_days,
                'annual_used'  => $balance->annual_used,
                'medical_used' => $balance->medical_used,
                'unpaid_used'  => $balance->unpaid_used,
            ],
        ]);
    }

    public string $commandSignature = 'leave:generate-balances';

    public function asCommand(Command $command): int
    {
        $today     = now();
        $generated = 0;

        EmployeeContract::query()
            ->whereHas('employee', fn ($q) => $q->where('state', EmployeeStateEnum::WORKING))
            ->where('start_date', '<=', $today->toDateString())
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $today->toDateString());
            })
            ->with('leaveBalances')
            ->each(function (EmployeeContract $contract) use ($today, &$generated) {
                $periodStart = $this->periodStartFor($contract, $today);

                $exists = $contract->leaveBalances
                    ->where('period_start', $periodStart->toDateString())
                    ->isNotEmpty();

                if (!$exists) {
                    $this->handle($contract, $periodStart);
                    $generated++;
                }
            });

        $command->info("Generated {$generated} leave balance(s).");

        return 0;
    }
}
