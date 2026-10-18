<?php

namespace App\Actions\HumanResources\EmployeeBulkEmail\UI;

use App\Actions\HumanResources\EmployeeBulkEmail\SendEmployeeBulkEmail;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithHumanResourcesEditAuthorisation;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateEmployeeBulkEmail extends OrgAction
{
    use WithHumanResourcesEditAuthorisation;

    /**
     * @return Collection<int, array{id: int, name: string, job_title: string|null, has_email: bool, avatar: string|null}>
     */
    public function handle(Organisation $organisation): Collection
    {
        return $organisation->employees()
            ->where('state', EmployeeStateEnum::WORKING)
            ->with([...Employee::AVATAR_RELATIONS, 'jobPositions'])
            ->orderBy('contact_name')
            ->get()
            ->map(fn (Employee $employee): array => [
                'id'        => $employee->id,
                'name'      => $employee->contact_name ?: $employee->alias,
                'job_title' => Arr::get($employee->getAttributes(), 'job_title') ?: $employee->jobPositions->first()?->name,
                'has_email' => (bool) ($employee->work_email ?: $employee->email),
                'avatar'    => $employee->avatarUrl(),
            ])
            ->values();
    }

    public function htmlResponse(Collection $employees, ActionRequest $request): Response
    {
        $title           = __('New bulk email');
        $routeParameters = $request->route()->originalParameters();

        return Inertia::render(
            'Org/HumanResources/CreateEmployeeBulkEmail',
            [
                'breadcrumbs'      => $this->getBreadcrumbs($routeParameters),
                'title'            => $title,
                'pageHead'         => [
                    'icon'    => ['fal', 'fa-mail-bulk'],
                    'title'   => $title,
                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'tertiary',
                            'key'   => 'cancel',
                            'label' => __('Cancel'),
                            'route' => [
                                'name'       => 'grp.org.hr.bulk_emails.index',
                                'parameters' => $routeParameters,
                            ],
                        ],
                    ],
                ],
                'sendRoute'        => [
                    'name'       => 'grp.org.hr.bulk_emails.store',
                    'parameters' => $routeParameters,
                ],
                'employees'        => $employees,
                'attachmentLimits' => [
                    'max_files'          => SendEmployeeBulkEmail::MAX_ATTACHMENTS,
                    'max_file_kilobytes' => SendEmployeeBulkEmail::MAX_ATTACHMENT_KILOBYTES,
                ],
            ]
        );
    }

    public function asController(Organisation $organisation, ActionRequest $request): Collection
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation);
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            IndexEmployeeBulkEmails::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('New bulk email'),
                    ],
                ],
            ]
        );
    }
}
