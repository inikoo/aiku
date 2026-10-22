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
use App\Models\Goods\ProductDevelopmentDesignSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * Renames a design section or changes its description or packaging supplier.
 */
class UpdateProductDevelopmentDesignSection extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    private ProductDevelopmentDesignSection $section;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $productDevelopment = $this->section->productDevelopment;

        return $this->canViewProductDevelopment($request->user(), $productDevelopment)
            && $this->productDevelopmentAccess()->canManageArtwork($request->user(), $productDevelopment);
    }

    public function handle(ProductDevelopmentDesignSection $section, array $modelData): ProductDevelopmentDesignSection
    {
        $section->fill(collect(Arr::only($modelData, ['name', 'description', 'packaging_supplier']))
            ->map(fn ($value) => trim((string) $value) ?: null)
            ->all());
        if (Arr::has($modelData, 'needs_compliance_text')) {
            $section->needs_compliance_text = (bool) $modelData['needs_compliance_text'];
        }

        if ($section->isDirty()) {
            $old = Arr::only($section->getOriginal(), array_keys($section->getDirty()));
            $section->save();

            RecordProductDevelopmentActivity::record($section->productDevelopment, RecordProductDevelopmentActivity::DESIGN_SECTION_CHANGED, [...$old, 'section' => $old['name'] ?? $section->name], [...$section->only(array_keys($old)), 'section' => $section->name]);
        }

        return $section;
    }

    public function rules(): array
    {
        return collect(StoreProductDevelopmentDesignSection::sectionRules())
            ->map(fn (array $rules, string $key) => $key === 'name' ? ['sometimes', ...array_diff($rules, ['required'])] : $rules)
            ->all();
    }

    public function afterValidator(Validator $validator): void
    {
        $productDevelopment = $this->section->productDevelopment;
        if (ProductDevelopmentDesignRounds::isArtworkLocked($productDevelopment)) {
            $validator->errors()->add('name', __('The artwork is signed off and locked'));

            return;
        }

        if ($this->has('name') && $productDevelopment->designSections()->whereKeyNot($this->section->id)->whereRaw('lower(name) = ?', [mb_strtolower(trim((string) $this->get('name')))])->exists()) {
            $validator->errors()->add('name', __('There is already a section with this name'));
        }
    }

    public function asController(ProductDevelopmentDesignSection $productDevelopmentDesignSection, ActionRequest $request): ProductDevelopmentDesignSection
    {
        $this->section = $productDevelopmentDesignSection;
        $this->initialisationFromGroup($productDevelopmentDesignSection->productDevelopment->group, $request);

        return $this->handle($productDevelopmentDesignSection, $this->validatedData);
    }

    public function action(ProductDevelopmentDesignSection $section, array $modelData): ProductDevelopmentDesignSection
    {
        $this->asAction = true;
        $this->section  = $section;
        $this->initialisationFromGroup($section->productDevelopment->group, $modelData);

        return $this->handle($section, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
