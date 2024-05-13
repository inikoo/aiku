<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 08:23:57 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect\Queries;

use App\Actions\Helpers\Query\Hydrators\QueryHydrateCount;
use App\Actions\Helpers\Query\UpdateQuery;
use App\Actions\Traits\WithProspectPrepareForValidation;
use App\Enums\CRM\Prospect\ProspectStateEnum;
use App\Models\Helpers\Query;
use App\Models\CRM\Prospect;
use App\Models\Catalogue\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateProspectQuery
{
    use AsAction;
    use WithAttributes;
    use WithProspectPrepareForValidation;

    private bool $asAction = false;
    /**
     * @var \App\Models\Catalogue\Shop
     */
    private Shop $scope;

    public function handle(Shop $shop, Query $query, array $modelData): Query
    {
        $data         = [];
        $queryBuilder = Arr::get($modelData, 'query_builder');

        if ($queryBuilder) {

            $data = [
                [
                    'model_type' => class_basename(Prospect::class),
                    'constrains' => [
                        'with'  => $queryBuilder['query'],
                        'group' => [
                            'where' => [
                                'state',
                                '=',
                                ProspectStateEnum::NO_CONTACTED->value
                            ],
                        ],
                        'filter' => [
                            $queryBuilder['tag']['state'] => $queryBuilder['tag']['tags']
                        ],
                    ],
                    'arguments' => $queryBuilder['prospect_last_contacted']['state'] ? [
                        '__date__' => [
                            'type'  => 'dateSubtraction',
                            'value' => [
                                'unit'     => $queryBuilder['prospect_last_contacted']['argument']['unit'],
                                'quantity' => $queryBuilder['prospect_last_contacted']['argument']['quantity']
                            ]
                        ]
                    ] : []
                ],
            ];
            if ($queryBuilder['prospect_last_contacted']['state']) {
                $lastContacted = [
                    'last_contacted_at',
                    '<=',
                    '__date__'
                ];
                $data[0]['constrains']['group']['orGroup']['where'] = $lastContacted;
            }
        }

        if (Arr::get($modelData, 'name')) {
            $data[]['slug'] = Str::slug($modelData['name']);
            $data[]['name'] = $modelData['name'];
        }

        foreach ($data as $queryBuilderData) {
            $queryBuilderData['is_seeded']   = true;
            $queryBuilderData['parent_type'] = 'Shop';
            $queryBuilderData['parent_id']   = $shop->id;

            $query = UpdateQuery::run($query, $queryBuilderData);
            QueryHydrateCount::run($query);
        }

        return $query;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("crm.edit");
    }

    public function htmlResponse(Query $query): RedirectResponse
    {
        return redirect()->route('grp.org.shops.show.crm.prospects.lists.edit', [
            'shop'  => $this->scope->slug,
            'query' => $query->slug,
        ]);
    }

    public function inShop(Shop $shop, Query $query, ActionRequest $request): Query
    {
        $this->scope = $shop;
        $this->fillFromRequest($request);

        return $this->handle($shop, $query, $this->validateAttributes());
    }

    public function rules(ActionRequest $request): array
    {
        return [
            'name'                                     => ['sometimes', 'string', 'max:255'],
            'query_builder.query'                      => ['sometimes'],
            'query_builder.tag.state'                  => ['sometimes', 'string'],
            'query_builder.tag.tags'                   => ['sometimes', 'array'],
            'query_builder.last_contact.state'         => ['sometimes', 'boolean'],
            'query_builder.last_contact.data.unit'     => ['sometimes:query_builder.last_contact.state,true'],
            'query_builder.last_contact.data.quantity' => ['sometimes:query_builder.last_contact.state,true']
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'query_builder.query.required'                         => __('The query is required'),
            'query_builder.last_contact.state.required'            => __('The last contact state is required'),
            'query_builder.last_contact.data.unit.required_if'     => __('The last contact unit is required'),
            'query_builder.last_contact.data.quantity.required_if' => __('The last contact quantity is required'),
        ];
    }
}
