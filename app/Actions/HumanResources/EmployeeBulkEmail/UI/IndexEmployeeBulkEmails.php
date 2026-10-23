<?php

namespace App\Actions\HumanResources\EmployeeBulkEmail\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithHumanResourcesAuthorisation;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Http\Resources\HumanResources\EmployeeBulkEmailsResource;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\EmployeeBulkEmail;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexEmployeeBulkEmails extends OrgAction
{
    use WithHumanResourcesAuthorisation;

    public const int RECIPIENTS_SAMPLE_SIZE = 5;

    /**
     * @param  array{from?: string|null, to?: string|null}  $modelData
     */
    public function handle(Organisation $organisation, array $modelData = []): LengthAwarePaginator
    {
        $from = $modelData['from'] ?? null;
        $to   = $modelData['to'] ?? null;

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('employee_bulk_emails.subject', $value)
                    ->orWhereRaw(
                        "regexp_replace(employee_bulk_emails.body, '<[^>]*>', ' ', 'g') ilike ?",
                        ['%'.addcslashes($value, '%_\\').'%']
                    );
            });
        });

        return QueryBuilder::for(EmployeeBulkEmail::class)
            ->where('employee_bulk_emails.organisation_id', $organisation->id)
            ->when($from, fn ($query) => $query->whereDate('employee_bulk_emails.created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('employee_bulk_emails.created_at', '<=', $to))
            ->with('sender.image')
            ->defaultSort('-created_at')
            ->allowedSorts(['created_at', 'subject', 'number_recipients'])
            ->allowedFilters([$globalSearch])
            ->withPaginator(null, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function rules(): array
    {
        return [
            'filter'        => ['sometimes', 'array'],
            'filter.global' => ['sometimes', 'nullable', 'string', 'max:255'],
            'from'          => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'to'            => ['sometimes', 'nullable', 'date_format:Y-m-d'],
        ];
    }

    private function attachRecipientSamples(LengthAwarePaginator $employeeBulkEmails): void
    {
        $samples = collect($employeeBulkEmails->items())->mapWithKeys(fn (EmployeeBulkEmail $employeeBulkEmail): array => [
            $employeeBulkEmail->id => array_slice($employeeBulkEmail->recipients ?? [], 0, self::RECIPIENTS_SAMPLE_SIZE),
        ]);

        $avatars = Employee::avatarUrlsFor($samples->flatten(1)->pluck('employee_id'));

        foreach ($employeeBulkEmails->items() as $employeeBulkEmail) {
            $employeeBulkEmail->setRelation('recipientsSample', collect($samples->get($employeeBulkEmail->id))->map(fn (array $recipient): array => [
                'id'     => $recipient['employee_id'],
                'name'   => $recipient['name'],
                'avatar' => $avatars->get($recipient['employee_id']),
            ]));
        }
    }

    public function htmlResponse(LengthAwarePaginator $employeeBulkEmails, ActionRequest $request): Response
    {
        $title = __('Bulk emails');

        $this->attachRecipientSamples($employeeBulkEmails);

        return Inertia::render(
            'Org/HumanResources/EmployeeBulkEmails',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'       => $title,
                'pageHead'    => [
                    'icon'    => ['fal', 'fa-mail-bulk'],
                    'title'   => $title,
                    'actions' => $this->canEdit ? [
                        [
                            'type'  => 'button',
                            'style' => 'create',
                            'key'   => 'new bulk email',
                            'label' => __('New bulk email'),
                            'icon'  => ['fal', 'fa-plus'],
                            'route' => [
                                'name'       => 'grp.org.hr.bulk_emails.create',
                                'parameters' => $request->route()->originalParameters(),
                            ],
                        ],
                    ] : [],
                ],
                'data'        => EmployeeBulkEmailsResource::collection($employeeBulkEmails),
                'filters'     => [
                    'search' => (string) $request->input('filter.global', ''),
                    'from'   => $this->validatedData['from'] ?? null,
                    'to'     => $this->validatedData['to'] ?? null,
                ],
            ]
        );
    }

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation, $this->validatedData);
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            ShowHumanResourcesDashboard::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.hr.bulk_emails.index',
                            'parameters' => $routeParameters,
                        ],
                        'label' => __('Bulk emails'),
                        'icon'  => 'fal fa-mail-bulk',
                    ],
                ],
            ]
        );
    }
}
