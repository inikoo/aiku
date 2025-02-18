<?php

/*
 * author Arya Permana - Kirin
 * created on 20-11-2024-08h-53m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Accounting\InvoiceCategory;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\Accounting\InvoiceCategory\InvoiceCategoryStateEnum;
use App\Enums\Accounting\InvoiceCategory\InvoiceCategoryTypeEnum;
use App\Models\Accounting\InvoiceCategory;
use App\Models\SysAdmin\Group;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreInvoiceCategory extends OrgAction
{
    use WithNoStrictRules;

    /**
     * @throws \Throwable
     */
    public function handle(Group $group, array $modelData): InvoiceCategory
    {
        return DB::transaction(function () use ($group, $modelData) {
            /** @var InvoiceCategory $invoiceCategory */
            $invoiceCategory = $group->invoiceCategories()->create($modelData);
            $invoiceCategory->stats()->create();
            $invoiceCategory->orderingIntervals()->create();
            $invoiceCategory->salesIntervals()->create();

            return $invoiceCategory;
        });
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return false;
    }


    public function rules(): array
    {
        $rules = [
            'name'               => ['required', 'string'],
            'state'              => ['sometimes', Rule::enum(InvoiceCategoryStateEnum::class)],
            'data'               => ['sometimes', 'array'],
            'settings'           => ['sometimes', 'array'],
            'currency_id'        => ['required', 'integer', 'exists:currencies,id'],
            'priority'           => ['required', 'integer'],
            'type'               => ['required', Rule::enum(InvoiceCategoryTypeEnum::class)],
            'show_in_dashboards' => ['sometimes', 'boolean'],
            'organisation_id'    => ['nullable', 'integer', Rule::exists('organisations', 'id')->where('group_id', $this->group->id)],
        ];
        if (!$this->strict) {
            $rules['state'] = ['required', Rule::enum(InvoiceCategoryStateEnum::class)];
            $rules          = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }

    /**
     * @throws \Throwable
     */
    public function action(Group $group, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true): InvoiceCategory
    {
        if (!$audit) {
            InvoiceCategory::disableAuditing();
        }
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromGroup($group, $modelData);

        return $this->handle($group, $this->validatedData);
    }


}
