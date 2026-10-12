<?php

namespace App\Actions\HumanResources\EmployeeBulkEmail\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithHumanResourcesAuthorisation;
use App\Http\Resources\HumanResources\EmployeeBulkEmailsResource;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\EmployeeBulkEmail;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowEmployeeBulkEmail extends OrgAction
{
    use WithHumanResourcesAuthorisation;

    public function handle(EmployeeBulkEmail $employeeBulkEmail): EmployeeBulkEmail
    {
        return $employeeBulkEmail->loadMissing('sender.image');
    }

    /**
     * @return array<int, array{id: int, name: string, avatar: string|null, job_title: string|null, is_working: bool}>
     */
    private function getRecipients(EmployeeBulkEmail $employeeBulkEmail): array
    {
        $recipients  = collect($employeeBulkEmail->recipients ?? []);
        $employeeIds = $recipients->pluck('employee_id');

        if ($employeeIds->isEmpty()) {
            return [];
        }

        $avatars   = Employee::avatarUrlsFor($employeeIds);
        $employees = Employee::query()
            ->whereIn('id', $employeeIds)
            ->select(['id', 'job_title', 'state'])
            ->with('jobPositions')
            ->get()
            ->keyBy('id');

        return $recipients->map(function (array $recipient) use ($avatars, $employees): array {
            /** @var Employee|null $employee */
            $employee = $employees->get($recipient['employee_id']);

            return [
                'id'         => $recipient['employee_id'],
                'name'       => $recipient['name'],
                'avatar'     => $avatars->get($recipient['employee_id']),
                'job_title'  => $employee ? (Arr::get($employee->getAttributes(), 'job_title') ?: $employee->jobPositions->first()?->name) : null,
                'is_working' => $employee?->state === EmployeeStateEnum::WORKING,
            ];
        })->all();
    }

    public function htmlResponse(EmployeeBulkEmail $employeeBulkEmail, ActionRequest $request): Response
    {
        $routeParameters = $request->route()->originalParameters();
        $indexParameters = ['organisation' => $this->organisation->slug];

        return Inertia::render(
            'Org/HumanResources/EmployeeBulkEmail',
            [
                'breadcrumbs' => $this->getBreadcrumbs($employeeBulkEmail, $routeParameters),
                'title'       => $employeeBulkEmail->subject,
                'pageHead'    => [
                    'icon'    => ['fal', 'fa-envelope'],
                    'title'   => $employeeBulkEmail->subject,
                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'tertiary',
                            'key'   => 'back',
                            'label' => __('Back'),
                            'route' => [
                                'name'       => 'grp.org.hr.bulk_emails.index',
                                'parameters' => $indexParameters,
                            ],
                        ],
                    ],
                ],
                'email'       => EmployeeBulkEmailsResource::make($employeeBulkEmail)->resolve($request),
                'recipients'  => $this->getRecipients($employeeBulkEmail),
            ]
        );
    }

    public function asController(Organisation $organisation, EmployeeBulkEmail $employeeBulkEmail, ActionRequest $request): EmployeeBulkEmail
    {
        $this->initialisation($organisation, $request);

        abort_unless($employeeBulkEmail->organisation_id === $organisation->id, 404);

        return $this->handle($employeeBulkEmail);
    }

    public function getBreadcrumbs(EmployeeBulkEmail $employeeBulkEmail, array $routeParameters): array
    {
        return array_merge(
            IndexEmployeeBulkEmails::make()->getBreadcrumbs(['organisation' => $routeParameters['organisation']]),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.hr.bulk_emails.show',
                            'parameters' => $routeParameters,
                        ],
                        'label' => Str::limit($employeeBulkEmail->subject, 50),
                    ],
                ],
            ]
        );
    }
}
