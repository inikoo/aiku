<?php

/*
 * Author Louis Perez
 * Created on 16-10-2026-19h-40m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStageEnum;
use App\Models\Goods\ProductDevelopmentStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class UpdateProductDevelopmentStageDueDate extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    public const string STAGE_DUE_CHANGED = 'stage_due_changed';

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        /** @var ProductDevelopmentStage $stage */
        $stage = $request->route('productDevelopmentStage');

        return $this->canViewProductDevelopment($request->user(), $stage->productDevelopment)
            && $this->canEditProductDevelopment($request->user(), $stage->productDevelopment);
    }

    public function handle(ProductDevelopmentStage $stage, ?string $dueAt): ProductDevelopmentStage
    {
        $previous = $stage->due_at?->toDateString();
        $stage->update(['due_at' => $dueAt]);

        RecordProductDevelopmentActivity::record(
            $stage->productDevelopment,
            self::STAGE_DUE_CHANGED,
            ['stage' => ProductDevelopmentStageEnum::labels()[$stage->stage->value], 'due_at' => $previous],
            ['stage' => ProductDevelopmentStageEnum::labels()[$stage->stage->value], 'due_at' => $stage->due_at?->toDateString()],
        );

        return $stage;
    }

    public function rules(): array
    {
        return [
            'due_at' => ['present', 'nullable', 'date'],
        ];
    }

    public function asController(ProductDevelopmentStage $productDevelopmentStage, ActionRequest $request): ProductDevelopmentStage
    {
        $this->initialisationFromGroup($productDevelopmentStage->productDevelopment->group, $request);

        return $this->handle($productDevelopmentStage, $this->validatedData['due_at'] ? Carbon::parse($this->validatedData['due_at'])->toDateString() : null);
    }

    public function action(ProductDevelopmentStage $stage, ?string $dueAt): ProductDevelopmentStage
    {
        $this->asAction = true;
        $this->initialisationFromGroup($stage->productDevelopment->group, ['due_at' => $dueAt]);

        return $this->handle($stage, $this->validatedData['due_at'] ? Carbon::parse($this->validatedData['due_at'])->toDateString() : null);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
