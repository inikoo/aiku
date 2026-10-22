<?php

/*
 * Author Louis Perez
 * Created on 22-10-2026-15h-00m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Design;

use App\Actions\Goods\ProductDevelopment\RecordProductDevelopmentActivity;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentDesignSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class StoreProductDevelopmentDesignSection extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    private ProductDevelopment $productDevelopment;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $this->canViewProductDevelopment($request->user(), $this->productDevelopment)
            && $this->productDevelopmentAccess()->canManageArtwork($request->user(), $this->productDevelopment);
    }

    public function handle(ProductDevelopment $productDevelopment, array $modelData): ProductDevelopmentDesignSection
    {
        /** @var ProductDevelopmentDesignSection $section */
        $section = $productDevelopment->designSections()->create([
            'name'               => trim($modelData['name']),
            'description'        => trim((string) ($modelData['description'] ?? '')) ?: null,
            'packaging_supplier' => trim((string) ($modelData['packaging_supplier'] ?? '')) ?: null,
            'editor_kind'        => $modelData['editor_kind'] ?? ProductDevelopmentDesignLayout::KIND_SHEET,
            'needs_compliance_text' => (bool) ($modelData['needs_compliance_text'] ?? false),
            'position'           => (int) $productDevelopment->designSections()->reorder()->max('position') + 1,
        ]);

        RecordProductDevelopmentActivity::record($productDevelopment, RecordProductDevelopmentActivity::DESIGN_SECTION_ADDED, [], ['section' => $section->name]);

        return $section;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function sectionRules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:64'],
            'description'        => ['sometimes', 'nullable', 'string', 'max:255'],
            'packaging_supplier' => ['sometimes', 'nullable', 'string', 'max:255'],
            'needs_compliance_text' => ['sometimes', 'boolean'],
        ];
    }

    public function rules(): array
    {
        return [
            ...self::sectionRules(),
            'editor_kind' => ['sometimes', Rule::in([ProductDevelopmentDesignLayout::KIND_BOX, ProductDevelopmentDesignLayout::KIND_SHEET])],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if (ProductDevelopmentDesignRounds::isArtworkLocked($this->productDevelopment)) {
            $validator->errors()->add('name', __('The artwork is signed off and locked'));

            return;
        }

        if ($this->productDevelopment->designSections()->whereRaw('lower(name) = ?', [mb_strtolower(trim((string) $this->get('name')))])->exists()) {
            $validator->errors()->add('name', __('There is already a section with this name'));
        }
    }

    public function asController(ProductDevelopment $productDevelopment, ActionRequest $request): ProductDevelopmentDesignSection
    {
        $this->productDevelopment = $productDevelopment;
        $this->initialisationFromGroup($productDevelopment->group, $request);

        return $this->handle($productDevelopment, $this->validatedData);
    }

    public function action(ProductDevelopment $productDevelopment, array $modelData): ProductDevelopmentDesignSection
    {
        $this->asAction           = true;
        $this->productDevelopment = $productDevelopment;
        $this->initialisationFromGroup($productDevelopment->group, $modelData);

        return $this->handle($productDevelopment, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
