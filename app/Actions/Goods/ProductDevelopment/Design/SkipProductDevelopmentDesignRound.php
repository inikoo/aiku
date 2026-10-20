<?php

/*
 * Author Louis Perez
 * Created on 18-10-2026-03h-00m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Design;

use App\Actions\Goods\ProductDevelopment\CreateProductDevelopmentTask;
use App\Actions\Goods\ProductDevelopment\RecordProductDevelopmentActivity;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * Packaging or label can be left out of a development that does not need them. A skipped round counts
 * as done for the next round and the stage gate; the skip can be undone until the next round is approved.
 */
class SkipProductDevelopmentDesignRound extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    public const string SKIP = 'skip';
    public const string UNDO = 'undo';

    private ProductDevelopment $productDevelopment;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $productDevelopment = $request->route('productDevelopment');

        return $this->canViewProductDevelopment($request->user(), $productDevelopment)
            && $this->productDevelopmentAccess()->canEdit($request->user(), $productDevelopment);
    }

    public function handle(ProductDevelopment $productDevelopment, User $user, array $modelData): ProductDevelopment
    {
        $round = ProductDevelopmentDesignRoundEnum::from($modelData['round']);
        $data  = $productDevelopment->data ?? [];
        $skip  = $modelData['operation'] === self::SKIP;

        if ($skip) {
            $data['skipped_rounds'][$round->value] = ['user_id' => $user->id, 'name' => $user->contact_name ?: $user->username, 'at' => now()->toIso8601String()];
        } else {
            unset($data['skipped_rounds'][$round->value]);
        }
        $productDevelopment->updateQuietly(['data' => $data]);

        RecordProductDevelopmentActivity::record($productDevelopment, $skip ? RecordProductDevelopmentActivity::DESIGN_ROUND_SKIPPED : RecordProductDevelopmentActivity::DESIGN_ROUND_UNSKIPPED, [], ['round' => $round->value]);

        CreateProductDevelopmentTask::make()->ensureForDevelopment($productDevelopment->refresh(), $user);

        return $productDevelopment;
    }

    public function rules(): array
    {
        return [
            'round'     => ['required', Rule::enum(ProductDevelopmentDesignRoundEnum::class)],
            'operation' => ['required', Rule::in([self::SKIP, self::UNDO])],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        $round = ProductDevelopmentDesignRoundEnum::tryFrom((string) $this->get('round'));
        if (!$round) {
            return;
        }

        $productDevelopment = $this->productDevelopment;
        $error              = match (true) {
            !$round->canBeSkipped() => __('The :round cannot be skipped', ['round' => mb_strtolower(ProductDevelopmentDesignRoundEnum::labels()[$round->value])]),
            ProductDevelopmentDesignRounds::isArtworkLocked($productDevelopment) => __('The artwork is signed off and locked'),
            $this->get('operation') === self::SKIP && ProductDevelopmentDesignRounds::isApproved($productDevelopment, $round) => __('This round is already approved'),
            $this->get('operation') === self::SKIP && ProductDevelopmentDesignRounds::isSkipped($productDevelopment, $round) => __('This round is already skipped'),
            $this->get('operation') === self::UNDO && !ProductDevelopmentDesignRounds::isSkipped($productDevelopment, $round) => __('This round is not skipped'),
            $this->get('operation') === self::UNDO && $round->next() && ProductDevelopmentDesignRounds::isApproved($productDevelopment, $round->next()) => __('The next round is already approved'),
            default => null,
        };

        if ($error) {
            $validator->errors()->add('round', $error);
        }
    }

    public function asController(ProductDevelopment $productDevelopment, ActionRequest $request): ProductDevelopment
    {
        $this->productDevelopment = $productDevelopment;
        $this->initialisationFromGroup($productDevelopment->group, $request);

        return $this->handle($productDevelopment, $request->user(), $this->validatedData);
    }

    public function action(ProductDevelopment $productDevelopment, User $user, array $modelData): ProductDevelopment
    {
        $this->asAction           = true;
        $this->productDevelopment = $productDevelopment;
        $this->initialisationFromGroup($productDevelopment->group, $modelData);

        return $this->handle($productDevelopment, $user, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
