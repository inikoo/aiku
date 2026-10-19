<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Aug 2026 15:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\ManufactureTaskSession\UI;

use App\Actions\OrgAction;
use App\Actions\Production\Production\UI\ShowArtisansDashboard;
use App\Enums\Production\Artisan\ArtisanPayTypeEnum;
use App\Enums\Production\JobOrder\JobOrderStateEnum;
use App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionActivityTypeEnum;
use App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum;
use App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionUnderTargetReasonEnum;
use App\Models\Production\ArtefactManufactureTask;
use App\Models\Production\JobOrderItemTask;
use App\Models\Production\ManufactureTask;
use App\Models\Production\ManufactureTaskSession;
use App\Models\Production\Production;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class IndexArtisans extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->authTo([
            'org-supervisor.'.$this->organisation->id,
            "productions_operations.{$this->production->id}.orchestrate",
        ]);

        return $request->user()->authTo([
            'org-supervisor.'.$this->organisation->id,
            'productions-view.'.$this->organisation->id,
            "productions_operations.{$this->production->id}.view",
        ]);
    }

    public function asController(Organisation $organisation, Production $production, ActionRequest $request): Production
    {
        $this->initialisationFromProduction($production, $request);

        return $this->handle($production);
    }

    public function handle(Production $production): Production
    {
        return $production;
    }

    public function rules(): array
    {
        return [
            'from'                => ['sometimes', 'date'],
            'to'                  => ['sometimes', 'date', 'after_or_equal:from'],
            'manufacture_task_id' => ['sometimes', 'integer'],
            'under_target'        => ['sometimes', 'boolean'],
            'artisan_pay_type'    => ['sometimes', 'nullable', Rule::enum(ArtisanPayTypeEnum::class)],
        ];
    }

    public function htmlResponse(Production $production, ActionRequest $request): Response
    {
        $from             = Carbon::parse(Arr::get($this->validatedData, 'from', now()->startOfWeek()->toDateString()))->startOfDay();
        $to               = Carbon::parse(Arr::get($this->validatedData, 'to', now()->toDateString()))->endOfDay();
        $manufactureTaskId = Arr::get($this->validatedData, 'manufacture_task_id');
        $underTargetOnly   = (bool) Arr::get($this->validatedData, 'under_target', false);
        $payType           = Arr::get($this->validatedData, 'artisan_pay_type');

        $sessions = ManufactureTaskSession::where('manufacture_task_sessions.production_id', $production->id)
            ->whereIn('manufacture_task_sessions.state', [
                ManufactureTaskSessionStateEnum::CLOSED,
                ManufactureTaskSessionStateEnum::VOIDED,
            ])
            ->whereBetween('ended_at', [$from, $to])
            ->when($manufactureTaskId, fn ($query) => $query->where('manufacture_task_id', $manufactureTaskId))
            ->when($underTargetOnly, fn ($query) => $query->where('is_under_target', true))
            ->when($payType, fn ($query) => $query->where('artisan_pay_type', $payType))
            ->with(['user', 'employee', 'manufactureTask', 'underTargetReviewer', 'jobOrderItemTask.jobOrderItem.artefact', 'jobOrderItemTask.jobOrderItem.tasks.manufactureTask', 'jobOrderItemTask.jobOrder', 'jobOrder'])
            ->orderByDesc('ended_at')
            ->get();

        $totals = function (Collection $sessions): array {
            $closed = $sessions->where('state', ManufactureTaskSessionStateEnum::CLOSED);

            return [
                'number_sessions'   => $closed->count(),
                'hours'             => round($closed->sum(fn (ManufactureTaskSession $session) => $session->paidHours()), 2),
                'quantity_made'     => (float)$closed->sum('quantity_made'),
                'quantity_rejected' => (float)$closed->sum('quantity_rejected'),
                'earned'            => round($closed->sum(fn (ManufactureTaskSession $session) => $session->quantity_made * ($session->task_work_cost ?? 0)), 2),
            ];
        };

        $packSizes = ArtefactManufactureTask::packSizes();
        $canCorrect = fn (ManufactureTaskSession $session) => $this->canEdit
            && $session->state == ManufactureTaskSessionStateEnum::CLOSED
            && !$session->is_combined
            && in_array($session->jobOrderItemTask->jobOrder->state, JobOrderStateEnum::open())
            && $session->jobOrderItemTask->jobOrderItem->quantity_received <= 0;

        $serializeSession = fn (ManufactureTaskSession $session) => [
            'id'                  => $session->id,
            'state'               => $session->state,
            'task_name'           => $session->manufactureTask->name,
            'artefact_code'       => $session->artefactCode(),
            'job_order_reference' => $session->jobOrderItemTask->jobOrder->reference,
            'started_at'          => $session->started_at,
            'ended_at'            => $session->ended_at,
            'break_minutes'       => (int)$session->break_minutes,
            'quantity_made'       => (float)$session->quantity_made,
            'pack_size'           => $packSizes[$session->jobOrderItemTask->jobOrderItem->artefact_id.':'.$session->manufacture_task_id] ?? null,
            'correct_route'       => $canCorrect($session) ? [
                'name'       => 'grp.models.manufacture-task-session.correct',
                'parameters' => ['manufactureTaskSession' => $session->id],
            ] : null,
            'other_steps'         => $canCorrect($session) ? $session->jobOrderItemTask->jobOrderItem->tasks
                ->reject(fn (JobOrderItemTask $step) => $step->id == $session->job_order_item_task_id || $step->combined_task_id)
                ->map(fn (JobOrderItemTask $step) => [
                    'id'                => $step->id,
                    'task_name'         => $step->manufactureTask->name,
                    'unit'              => $step->unit(),
                    'pack_size'         => $step->packSize(),
                    'quantity_left'     => max(0, (float)$step->quantity_required - (float)$step->quantity_made),
                ])->values()->all() : [],
            'quantity_rejected'   => (float)$session->quantity_rejected,
            'earned'              => round($session->quantity_made * ($session->task_work_cost ?? 0), 2),
            'units_per_hour'      => $session->paidHours() > 0 ? round($session->quantity_made / $session->paidHours(), 1) : null,
            'standard_rate'       => $session->standard_rate === null ? null : (float)$session->standard_rate,
            'is_under_target'     => $session->is_under_target && $session->state == ManufactureTaskSessionStateEnum::CLOSED,
            'is_hourly'           => $session->artisan_pay_type == ArtisanPayTypeEnum::HOURLY,
            'under_target_review' => $session->under_target_reviewed_at ? [
                'reason'      => ManufactureTaskSessionUnderTargetReasonEnum::labels()[$session->under_target_reason?->value] ?? null,
                'note'        => $session->under_target_note,
                'reviewed_by' => $session->underTargetReviewer?->contact_name ?: $session->underTargetReviewer?->username,
                'reviewed_at' => $session->under_target_reviewed_at,
            ] : null,
            'review_route'        => $this->canEdit && $session->is_under_target && $session->state == ManufactureTaskSessionStateEnum::CLOSED ? [
                'name'       => 'grp.models.manufacture-task-session.under_target_review',
                'parameters' => ['manufactureTaskSession' => $session->id],
            ] : null,
            'void_route'          => $this->canEdit && $session->state == ManufactureTaskSessionStateEnum::CLOSED ? [
                'name'       => 'grp.models.manufacture-task-session.void',
                'parameters' => ['manufactureTaskSession' => $session->id],
            ] : null,
        ];

        $artisans = $sessions
            ->groupBy('user_id')
            ->map(function (Collection $allUserSessions) use ($totals, $serializeSession) {
                /** @var ManufactureTaskSession $first */
                $first         = $allUserSessions->first();
                $userSessions  = $allUserSessions->reject(fn (ManufactureTaskSession $session) => $session->isNonProductive());
                $artisanTotals = $totals($userSessions);
                $employee      = $allUserSessions->first(fn (ManufactureTaskSession $session) => $session->employee)?->employee;

                return [
                    'user_id'           => $first->user_id,
                    'worker'            => $first->user->contact_name ?: $first->user->username,
                    'artisan_pay_type'  => $employee?->artisan_pay_type,
                    'pay_type_route'    => $this->canEdit && $employee?->organisation_id == $this->organisation->id ? [
                        'name'       => 'grp.org.productions.show.artisans.pay_type.update',
                        'parameters' => ['organisation' => $this->organisation->slug, 'production' => $this->production->slug, 'employee' => $employee->id],
                    ] : null,
                    'number_sessions'   => $artisanTotals['number_sessions'],
                    'hours_worked'      => $artisanTotals['hours'],
                    'quantity_made'     => $artisanTotals['quantity_made'],
                    'quantity_rejected' => $artisanTotals['quantity_rejected'],
                    'earned'            => $artisanTotals['earned'],
                    'non_productive'    => $this->nonProductiveSummary($allUserSessions->filter(fn (ManufactureTaskSession $session) => $session->isNonProductive())),
                    'under_target_open' => $userSessions->filter(fn (ManufactureTaskSession $session) => $session->is_under_target && $session->state == ManufactureTaskSessionStateEnum::CLOSED && !$session->under_target_reviewed_at)->count(),
                    'jobs'              => $userSessions
                        ->groupBy('jobOrderItemTask.job_order_item_id')
                        ->map(function (Collection $jobSessions, int $jobOrderItemId) use ($serializeSession, $totals) {
                            /** @var ManufactureTaskSession $firstJobSession */
                            $firstJobSession = $jobSessions->first();

                            return [
                                'job_order_item_id'   => $jobOrderItemId,
                                'job_order_reference' => $firstJobSession->jobOrderItemTask->jobOrder->reference,
                                'artefact_code'       => $firstJobSession->jobOrderItemTask->jobOrderItem->artefact->code,
                                'steps'               => $jobSessions
                                    ->groupBy('manufacture_task_id')
                                    ->map(fn (Collection $stepSessions, int $manufactureTaskId) => [
                                        'manufacture_task_id' => $manufactureTaskId,
                                        'task_name'           => $stepSessions->first()->manufactureTask->name,
                                        'sessions'            => $stepSessions->map($serializeSession)->values(),
                                    ] + $totals($stepSessions))
                                    ->values(),
                            ] + $totals($jobSessions);
                        })
                        ->values(),
                ];
            })
            ->sortByDesc(fn (array $artisan) => [$artisan['quantity_made'], $artisan['non_productive']['hours']])
            ->values();

        return Inertia::render(
            'Org/Production/Artisans',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'       => __('Artisans'),
                'pageHead'    => [
                    'title' => __('Artisans'),
                    'icon'  => [
                        'icon'  => ['fal', 'fa-user-hard-hat'],
                        'title' => __('Artisans'),
                    ],
                ],
                'period'   => [
                    'from' => $from->toDateString(),
                    'to'   => $to->toDateString(),
                ],
                'manufacture_task_id' => $manufactureTaskId ? (int)$manufactureTaskId : null,
                'under_target'        => $underTargetOnly,
                'artisan_pay_type'    => $payType,
                'artisan_pay_types'   => ArtisanPayTypeEnum::labels(),
                'under_target_reasons' => ManufactureTaskSessionUnderTargetReasonEnum::labels(),
                'non_productive_activities' => array_map(fn (ManufactureTaskSessionActivityTypeEnum $activity) => [
                    'value' => $activity->value,
                    'label' => ManufactureTaskSessionActivityTypeEnum::labels()[$activity->value],
                ], ManufactureTaskSessionActivityTypeEnum::floorActivities()),
                'manufacture_tasks'   => ManufactureTask::withTrashed()
                    ->where('production_id', $production->id)
                    ->orderBy('name')
                    ->get(['id', 'name']),
                'artisans' => $artisans,
            ]
        );
    }

    /**
     * Preparation and cleaning are kept out of units, targets and earnings: hours per activity by day, week and job order.
     *
     * @param Collection<int, ManufactureTaskSession> $sessions
     */
    private function nonProductiveSummary(Collection $sessions): array
    {
        $closed = $sessions->where('state', ManufactureTaskSessionStateEnum::CLOSED);

        $hoursByActivity = function (Collection $sessions): array {
            $hours = [];
            foreach (ManufactureTaskSessionActivityTypeEnum::floorActivities() as $activity) {
                $hours[$activity->value] = round($sessions->where('activity_type', $activity)->sum(fn (ManufactureTaskSession $session) => $session->paidHours()), 2);
            }

            return $hours;
        };

        $groupedHours = fn (callable $key, callable $label) => $closed
            ->groupBy($key)
            ->sortKeys()
            ->map(fn (Collection $group, string $groupKey) => ['key' => $groupKey, 'label' => $label($group->first(), $groupKey), 'hours' => $hoursByActivity($group)])
            ->values();

        return [
            'hours'      => round($closed->sum(fn (ManufactureTaskSession $session) => $session->paidHours()), 2),
            'pay'        => $closed->whereNotNull('pay')->isEmpty() ? null : round((float) $closed->sum('pay'), 2),
            'activities' => $hoursByActivity($closed),
            'days'       => $groupedHours(fn (ManufactureTaskSession $session) => $session->ended_at->toDateString(), fn ($session, string $date) => $date),
            'weeks'      => $groupedHours(fn (ManufactureTaskSession $session) => $session->ended_at->copy()->startOfWeek()->toDateString(), fn ($session, string $date) => $date),
            'job_orders' => $groupedHours(fn (ManufactureTaskSession $session) => (string) ($session->job_order_id ?? ''), fn (ManufactureTaskSession $session) => $session->jobOrder?->reference),
            'sessions'   => $sessions->map(fn (ManufactureTaskSession $session) => [
                'id'                  => $session->id,
                'state'               => $session->state,
                'activity'            => $session->activityLabel(),
                'job_order_reference' => $session->jobOrder?->reference,
                'started_at'          => $session->started_at,
                'ended_at'            => $session->ended_at,
                'break_minutes'       => (int) $session->break_minutes,
                'pay'                 => $session->pay === null ? null : (float) $session->pay,
                'void_route'          => $this->canEdit && $session->state == ManufactureTaskSessionStateEnum::CLOSED ? [
                    'name'       => 'grp.models.manufacture-task-session.void',
                    'parameters' => ['manufactureTaskSession' => $session->id],
                ] : null,
            ])->values(),
        ];
    }

    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        $routeParameters = Arr::only($routeParameters, ['organisation', 'production']);

        return array_merge(
            (new ShowArtisansDashboard())->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.productions.show.artisans.index',
                            'parameters' => $routeParameters,
                        ],
                        'label' => __('Artisans'),
                    ],
                    'suffix' => $suffix,
                ],
            ]
        );
    }
}
