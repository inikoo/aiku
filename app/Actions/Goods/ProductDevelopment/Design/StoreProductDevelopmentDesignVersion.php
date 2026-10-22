<?php

/*
 * Author Louis Perez
 * Created on 17-10-2026-10h-50m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Design;

use App\Actions\Goods\ProductDevelopment\CreateProductDevelopmentTask;
use App\Actions\Goods\ProductDevelopment\RecordProductDevelopmentActivity;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundStatusEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentDesignRound;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class StoreProductDevelopmentDesignVersion extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    private ProductDevelopment $productDevelopment;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $productDevelopment = $request->route('productDevelopment');

        return $this->canViewProductDevelopment($request->user(), $productDevelopment)
            && $this->productDevelopmentAccess()->canDesign($request->user(), $productDevelopment);
    }

    public const string SOURCE_EDITOR = 'editor';
    public const string SOURCE_UPLOAD = 'upload';

    /**
     * Starts the next concept version; the concept is always an uploaded PDF or picture.
     */
    public function handle(ProductDevelopment $productDevelopment, User $user, ProductDevelopmentDesignRoundEnum $round): ProductDevelopmentDesignRound
    {
        /** @var ProductDevelopmentDesignRound $designRound */
        $designRound = $productDevelopment->designRounds()->create([
            'round'   => $round,
            'version' => (int) ProductDevelopmentDesignRounds::scope($productDevelopment, $round)->max('version') + 1,
            'status'  => ProductDevelopmentDesignRoundStatusEnum::DRAFT,
            'data'    => ['source' => self::SOURCE_UPLOAD, 'upload' => null],
        ]);

        RecordProductDevelopmentActivity::record($productDevelopment, RecordProductDevelopmentActivity::DESIGN_VERSION_STARTED, [], [
            'round'   => $round->value,
            'version' => $designRound->version,
            'by'      => $user->contact_name ?: $user->username,
        ]);

        CreateProductDevelopmentTask::make()->syncProgress($productDevelopment, $user);

        return $designRound;
    }

    public function rules(): array
    {
        return [
            'round' => ['required', Rule::enum(ProductDevelopmentDesignRoundEnum::class)],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        $round = ProductDevelopmentDesignRoundEnum::tryFrom((string) $this->get('round'));
        if (!$round || ProductDevelopmentDesignRounds::canStartVersion($this->productDevelopment, $round)) {
            return;
        }

        $validator->errors()->add('round', ProductDevelopmentDesignRounds::isArtworkLocked($this->productDevelopment)
            ? __('The artwork is signed off and locked')
            : __('This round already has a version in progress or approved'));
    }

    public function asController(ProductDevelopment $productDevelopment, ActionRequest $request): ProductDevelopmentDesignRound
    {
        $this->productDevelopment = $productDevelopment;
        $this->initialisationFromGroup($productDevelopment->group, $request);

        return $this->handle($productDevelopment, $request->user(), ProductDevelopmentDesignRoundEnum::from($this->validatedData['round']));
    }

    public function action(ProductDevelopment $productDevelopment, User $user, array $modelData): ProductDevelopmentDesignRound
    {
        $this->asAction           = true;
        $this->productDevelopment = $productDevelopment;
        $this->initialisationFromGroup($productDevelopment->group, $modelData);

        return $this->handle($productDevelopment, $user, ProductDevelopmentDesignRoundEnum::from($this->validatedData['round']));
    }

    public function htmlResponse(ProductDevelopmentDesignRound $designRound): RedirectResponse
    {
        return Redirect::route('grp.product_developments.design', [
            'productDevelopment' => $designRound->productDevelopment->slug,
            'round'              => $designRound->round->value,
            'version'            => $designRound->version,
        ]);
    }
}
