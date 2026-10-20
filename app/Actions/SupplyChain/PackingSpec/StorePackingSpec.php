<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 09:40
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\SupplyChain\PackingSpec;

use App\Actions\Helpers\Media\SaveModelAttachment;
use App\Actions\OrgAction;
use App\Actions\Procurement\ProcurementNote\StoreProcurementNote;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteCategoryEnum;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteVisibilityEnum;
use App\Enums\SupplyChain\PackingSpec\PackingSpecLevelEnum;
use App\Enums\SupplyChain\PackingSpec\PackingSpecPackedByEnum;
use App\Enums\SupplyChain\PackingSpec\PackingSpecRoleEnum;
use App\Enums\SupplyChain\PackingSpec\PackingSpecStatusEnum;
use App\Models\Goods\PackagingComponent;
use App\Models\Helpers\Media;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\SupplyChain\PackingSpecLine;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\SupplyChain\SupplierProductPackingSpec;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * The agent declares how a supplier product is packed. Each save is a new version that supersedes the live one, so
 * deliveries keep the packing they were packed with. The opened-carton photo is optional, carried over from the
 * version before when no new one is sent. "Same packing as" copies the version to other products of the same agent.
 * A packaging note goes on the supplier so purchasing sees the change.
 */
class StorePackingSpec extends OrgAction
{
    use WithAgentOrganisation;
    use WithPackingCopyTargets;
    use WithProcurementEditAuthorisation;

    public const string PHOTO_SCOPE = 'opened_carton';

    private ?SupplierProduct $supplierProduct = null;

    public function handle(SupplierProduct $supplierProduct, array $modelData, ?User $user = null, ?Media $photo = null): SupplierProductPackingSpec
    {
        $lines = Arr::pull($modelData, 'lines', []);
        Arr::forget($modelData, ['photo', 'copy_to']);

        $spec = DB::transaction(function () use ($supplierProduct, $modelData, $lines, $user) {
            SupplierProduct::whereKey($supplierProduct->id)->lockForUpdate()->first();

            $previous = $supplierProduct->packingSpecs()->live()->get();
            $version  = (int)SupplierProductPackingSpec::where('supplier_product_id', $supplierProduct->id)->max('version') + 1;

            $components = PackagingComponent::whereIn('id', Arr::pluck($lines, 'packaging_component_id'))->get()->keyBy('id');
            $review     = $components->filter(fn (PackagingComponent $component) => $component->needs_review);

            /** @var SupplierProductPackingSpec $spec */
            $spec = $supplierProduct->packingSpecs()->create([
                ...$modelData,
                'group_id'             => $supplierProduct->group_id,
                'agent_id'             => Arr::get($modelData, 'agent_id', $supplierProduct->agent_id),
                'version'              => $version,
                'status'               => PackingSpecStatusEnum::SUBMITTED,
                'effective_from'       => Arr::get($modelData, 'effective_from') ?? now()->toDateString(),
                'needs_review'         => $review->isNotEmpty(),
                'review_reason'        => $review->isNotEmpty() ? __('Packing material not in the list: :materials', [
                    'materials' => collect($lines)->filter(fn (array $line) => $review->has($line['packaging_component_id']))->map(fn (array $line) => Arr::get($line, 'description') ?: __('Other'))->implode(', '),
                ]) : null,
                'submitted_by_user_id' => $user?->id,
                'submitted_at'         => now(),
            ]);

            foreach (array_values($lines) as $position => $line) {
                $component = $components->get($line['packaging_component_id']);
                $level     = PackingSpecLevelEnum::from($line['level']);
                $weight    = Arr::get($line, 'weight_g_each');

                $spec->lines()->create([
                    'packaging_component_id' => $component->id,
                    'level'                  => $level,
                    'role'                   => Arr::get($line, 'role') ?? $component->default_role ?? PackingSpecRoleEnum::WRAP,
                    'quantity'               => $line['quantity'],
                    'declared_weight_g_each' => $weight,
                    'weight_g_each'          => $weight,
                    'fate_at_our_warehouse'  => Arr::get($line, 'fate_at_our_warehouse') ?? $level->defaultFate(),
                    'is_epr_reportable'      => $component->is_epr_reportable,
                    'description'            => Arr::get($line, 'description'),
                    'position'               => $position,
                ]);
            }

            $this->weightCheck($spec->refresh());

            $previous->each(fn (SupplierProductPackingSpec $old) => $old->update(['status' => PackingSpecStatusEnum::SUPERSEDED]));

            return $spec;
        });

        if ($photo) {
            $spec->attachments()->attach($photo->id, ['group_id' => $spec->group_id, 'scope' => self::PHOTO_SCOPE, 'data' => '{}']);
        }

        return $spec;
    }

    /**
     * Soft check: packing material plus the goods should be within the tolerance of the carton gross weight, the
     * supplier product's or else the one the agent declared. A miss is kept on the spec as a warning, it does not stop
     * the save.
     */
    public function weightCheck(SupplierProductPackingSpec $spec): void
    {
        $packaging = $spec->packagingGramsPerCarton();
        $gross     = $spec->supplierProduct->carton_weight ?: $spec->declared_carton_gross_weight_g;
        $net       = $spec->cartonNetWeightG();
        $deviation = $gross && $net ? round(($net + $packaging - $gross) / $gross * 100, 2) : null;

        $spec->update([
            'packaging_weight_g'         => round($packaging, 3),
            'weight_check_deviation_pct' => $deviation,
        ]);
    }

    public function photoOf(SupplierProductPackingSpec $spec): ?Media
    {
        return $spec->attachments()->wherePivot('scope', self::PHOTO_SCOPE)->latest('model_has_attachments.id')->first();
    }

    public function rules(): array
    {
        return [
            'packed_by'                      => ['required', Rule::enum(PackingSpecPackedByEnum::class)],
            'effective_from'                 => ['sometimes', 'nullable', 'date'],
            'carton_length_mm'               => ['sometimes', 'nullable', 'integer', 'min:1'],
            'carton_width_mm'                => ['sometimes', 'nullable', 'integer', 'min:1'],
            'carton_height_mm'               => ['sometimes', 'nullable', 'integer', 'min:1'],
            'declared_carton_gross_weight_g' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'lines'                          => ['required', 'array', 'min:1'],
            'lines.*.packaging_component_id' => ['required', 'integer', Rule::exists('packaging_components', 'id')->where('group_id', $this->group->id)->where('is_dropdown_item', true)],
            'lines.*.level'                  => ['required', Rule::enum(PackingSpecLevelEnum::class)],
            'lines.*.role'                   => ['sometimes', 'nullable', Rule::enum(PackingSpecRoleEnum::class)],
            'lines.*.quantity'               => ['required', 'numeric', 'gt:0'],
            'lines.*.weight_g_each'          => ['sometimes', 'nullable', 'numeric', 'gt:0'],
            'lines.*.description'            => ['sometimes', 'nullable', 'string', 'max:255'],
            'photo'                          => ['sometimes', 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:20000'],
            'copy_to'                        => ['sometimes', 'array'],
            'copy_to.*'                      => ['integer'],
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'lines.required'                          => __('Add at least one packing material'),
            'lines.*.packaging_component_id.required' => __('Choose the packing material'),
            'lines.*.quantity.required'               => __('Enter how many go in'),
            'lines.*.quantity.gt'                     => __('Enter how many go in'),
            'lines.*.weight_g_each.gt'                => __('Enter the weight in grams'),
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        $components = PackagingComponent::whereIn('id', collect($this->get('lines', []))->pluck('packaging_component_id')->filter())->get()->keyBy('id');

        foreach ($this->get('lines', []) as $index => $line) {
            $component = $components->get(Arr::get($line, 'packaging_component_id'));
            if (!$component) {
                continue;
            }
            if ($component->is_epr_reportable && !Arr::get($line, 'weight_g_each')) {
                $validator->errors()->add("lines.$index.weight_g_each", __('Enter the weight of one :material in grams', ['material' => $component->dropdownLabel()]));
            }
            if ($component->needs_review && !Arr::get($line, 'description')) {
                $validator->errors()->add("lines.$index.description", __('Describe the packing material'));
            }
        }
    }

    private function previousPhoto(): ?Media
    {
        $live = $this->supplierProduct?->livePackingSpec;

        return $live ? $this->photoOf($live) : null;
    }

    public function asController(Organisation $organisation, OrgSupplierProduct $orgSupplierProduct, ActionRequest $request): RedirectResponse
    {
        $this->supplierProduct = $orgSupplierProduct->supplierProduct;
        $this->initialisation($organisation, $request);
        $this->authorizeProcurementRecord($orgSupplierProduct);

        $user  = $request->user();
        $data  = $this->validatedData;
        $agent = $orgSupplierProduct->orgAgent?->agent_id ?? $this->supplierProduct->agent_id;

        $upload = $this->get('photo') instanceof UploadedFile ? $this->get('photo') : null;
        $photo  = $upload ? null : $this->previousPhoto();
        $spec   = $this->handle($this->supplierProduct, [...$data, 'agent_id' => $agent], $user, $photo);
        $photo  ??= $upload ? $this->savePhoto($spec, $upload) : null;

        $copies = $this->copyTargets($orgSupplierProduct, Arr::get($data, 'copy_to', []))
            ->map(fn (OrgSupplierProduct $target) => $this->handle($target->supplierProduct, [...$data, 'agent_id' => $agent, 'copied_from_id' => $spec->id], $user, $photo));

        $this->postNote($spec, $copies, $user);

        return redirect()->back();
    }

    private function savePhoto(SupplierProductPackingSpec $spec, UploadedFile $file): Media
    {
        return SaveModelAttachment::make()->handle($spec, [
            'path'         => $file->getPathname(),
            'originalName' => $file->getClientOriginalName(),
            'extension'    => $file->getClientOriginalExtension(),
            'scope'        => self::PHOTO_SCOPE,
        ]);
    }

    /**
     * @param Collection<int, SupplierProductPackingSpec> $copies
     */
    private function postNote(SupplierProductPackingSpec $spec, Collection $copies, ?User $user): void
    {
        $supplierProduct = $spec->supplierProduct;
        $materials       = $spec->lines()->with('packagingComponent')->get()
            ->map(fn (PackingSpecLine $line) => sprintf('%s × %s (%s)', rtrim(rtrim(number_format((float)$line->quantity, 3, '.', ''), '0'), '.'), $line->description ?: $line->packagingComponent->dropdownLabel('en'), PackingSpecLevelEnum::labels()[$line->level->value]))
            ->implode("\n");

        $body = $materials;
        if ($copies->isNotEmpty()) {
            $body .= "\n\n".__('Same packing copied to: :codes', ['codes' => $copies->map(fn (SupplierProductPackingSpec $copy) => $copy->supplierProduct->code)->implode(', ')]);
        }

        rescue(fn () => StoreProcurementNote::make()->action($supplierProduct->supplier ?? $supplierProduct, [
            'title'          => __('Packing spec v:version for :code', ['version' => $spec->version, 'code' => $supplierProduct->code]),
            'body'           => $body,
            'category'       => ProcurementNoteCategoryEnum::PACKAGING->value,
            'visibility'     => ProcurementNoteVisibilityEnum::AGENT->value,
            'author_user_id' => $user?->id,
        ], strict: false));
    }
}
