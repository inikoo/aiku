<!--
  - Author Louis Perez
  - Created on 16-10-2026-23h-15m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import { router } from "@inertiajs/vue3"
import type { RequestPayload } from "@inertiajs/core"
import InputNumber from "primevue/inputnumber"
import InputText from "primevue/inputtext"
import Textarea from "primevue/textarea"
import Select from "primevue/select"
import SelectButton from "primevue/selectbutton"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { productDevelopmentColor } from "@/Components/Goods/ProductDevelopment/productDevelopmentColors"
import { formatChecklistValue, type ChecklistDimensions, type ChecklistRow } from "@/Components/Goods/ProductDevelopment/productDevelopmentChecklist"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCheckCircle, faCircle, faMinusCircle, faPencil, faSave, faBan, faUndo, faLink } from "@fal"
library.add(faCheckCircle, faCircle, faMinusCircle, faPencil, faSave, faBan, faUndo, faLink)

const props = defineProps<{
    row: ChecklistRow
    updateRoute: string
    countries: { value: number, label: string }[]
}>()

type Mode = "view" | "edit" | "not_applicable"

const mode = ref<Mode>("view")
const draft = ref<string | number | null>(null)
const dimensions = ref<ChecklistDimensions>({})
const naReason = ref("")
const saving = ref(false)
const errors = ref<Record<string, string>>({})

const stateIcon: Record<ChecklistRow["state"], { icon: string, class: string, label: string }> = {
    complete: { icon: "fal fa-check-circle", class: "text-green-600", label: "Complete" },
    not_applicable: { icon: "fal fa-minus-circle", class: "text-gray-400", label: "Not applicable" },
    missing: { icon: "fal fa-circle", class: "text-amber-500", label: "Missing" },
}

const unitOptions = [{ value: "mm", label: "mm" }, { value: "cm", label: "cm" }]

const displayValue = computed(() => formatChecklistValue(props.row, props.countries))

const savedLine = computed(() => {
    if (!props.row.saved_at) {
        return null
    }
    const when = useFormatTime(props.row.saved_at, { formatTime: "d MMM yyyy" })
    return props.row.saved_by ? ctrans("Saved :date by :user", { date: when, user: props.row.saved_by }) : ctrans("Saved :date", { date: when })
})

const canSubmit = computed(() => {
    if (saving.value) {
        return false
    }
    if (mode.value === "not_applicable") {
        return naReason.value.trim() !== ""
    }
    if (props.row.input_type === "dimensions") {
        return [dimensions.value.l, dimensions.value.w, dimensions.value.h].some((size) => Number(size ?? 0) > 0)
    }
    return draft.value !== null && String(draft.value).trim() !== ""
})

function startEdit() {
    errors.value = {}
    const value = props.row.value
    if (props.row.input_type === "dimensions") {
        const current = ((value && typeof value === "object" && !Array.isArray(value)) ? value : {}) as ChecklistDimensions
        dimensions.value = { l: current.l ?? null, w: current.w ?? null, h: current.h ?? null, units: current.units ?? "cm" }
    } else if (props.row.input_type === "integer" || props.row.input_type === "decimal" || props.row.input_type === "country") {
        draft.value = value === null || value === "" ? null : Number(value)
    } else {
        draft.value = (value as string | null) ?? ""
    }
    mode.value = "edit"
}

function startNotApplicable() {
    errors.value = {}
    naReason.value = props.row.na_reason ?? ""
    mode.value = "not_applicable"
}

function send(payload: RequestPayload) {
    errors.value = {}
    router.patch(route(props.updateRoute, { productDevelopmentChecklistItem: props.row.id }), payload, {
        preserveScroll: true,
        onStart: () => saving.value = true,
        onFinish: () => saving.value = false,
        onError: (validationErrors) => errors.value = validationErrors,
        onSuccess: () => mode.value = "view",
    })
}

function submit() {
    if (!canSubmit.value) {
        return
    }
    if (mode.value === "not_applicable") {
        send({ operation: "not_applicable", na_reason: naReason.value })
        return
    }
    send({ operation: "save", value: props.row.input_type === "dimensions" ? { ...dimensions.value, type: "rectangular" } : draft.value })
}

const reopen = () => send({ operation: "reopen" })

const role = computed(() => productDevelopmentColor(props.row.role.color))
const firstError = computed(() => Object.values(errors.value)[0] ?? null)
</script>

<template>
    <li class="py-2.5 text-sm">
        <div class="flex items-start gap-2">
            <FontAwesomeIcon :icon="stateIcon[row.state].icon" :class="stateIcon[row.state].class" class="mt-0.5 shrink-0" fixed-width :aria-label="ctrans(stateIcon[row.state].label)" />
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                    <span :class="row.state === 'missing' ? 'text-gray-900' : 'text-gray-600'">{{ row.label }}</span>
                    <span v-if="!row.required" class="text-xs text-gray-400">({{ ctrans("optional") }})</span>
                    <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                        <FontAwesomeIcon :icon="row.role.icon" :class="role.icon" fixed-width aria-hidden="true" />
                        {{ row.role.label }}
                    </span>
                </div>

                <template v-if="mode === 'view'">
                    <div v-if="row.state === 'not_applicable'" class="mt-0.5 text-xs text-gray-500">{{ ctrans("N/A") }}: {{ row.na_reason }}</div>
                    <div v-else-if="displayValue" class="mt-0.5 whitespace-pre-line text-gray-800">{{ displayValue }}</div>
                    <div v-if="row.needs_supplier_product" class="mt-0.5 flex items-center gap-1 text-xs text-amber-700">
                        <FontAwesomeIcon icon="fal fa-link" fixed-width aria-hidden="true" />
                        {{ ctrans("Link a supplier product to this SKU first") }}
                    </div>
                    <div v-if="savedLine && row.state !== 'missing'" class="mt-0.5 text-xs text-gray-400">{{ savedLine }}</div>
                </template>

                <div v-else class="mt-2 space-y-2">
                    <template v-if="mode === 'edit'">
                        <div v-if="row.input_type === 'dimensions'" class="flex flex-wrap items-center gap-2">
                            <InputNumber v-model="dimensions.l" :placeholder="ctrans('Length')" :min="0" :maxFractionDigits="2" size="small" inputClass="w-20" />
                            <span class="text-gray-400">×</span>
                            <InputNumber v-model="dimensions.w" :placeholder="ctrans('Width')" :min="0" :maxFractionDigits="2" size="small" inputClass="w-20" />
                            <span class="text-gray-400">×</span>
                            <InputNumber v-model="dimensions.h" :placeholder="ctrans('Height')" :min="0" :maxFractionDigits="2" size="small" inputClass="w-20" />
                            <SelectButton v-model="dimensions.units" :options="unitOptions" optionLabel="label" optionValue="value" :allowEmpty="false" size="small" />
                        </div>
                        <Select v-else-if="row.input_type === 'country'" v-model="draft" :options="countries" optionLabel="label" optionValue="value" filter size="small" :placeholder="ctrans('Choose a country')" class="w-full sm:w-72" overlayClass="text-sm" />
                        <Textarea v-else-if="row.input_type === 'textarea'" v-model="draft as string" rows="3" autoResize class="w-full text-sm" :invalid="!!firstError" />
                        <InputText v-else-if="row.input_type === 'text'" v-model="draft as string" size="small" class="w-full sm:w-72" :invalid="!!firstError" @keydown.enter="submit" />
                        <div v-else class="flex items-center gap-2">
                            <InputNumber
                                v-model="draft as number | null"
                                :min="0"
                                :useGrouping="false"
                                :maxFractionDigits="row.input_type === 'decimal' ? 4 : 0"
                                size="small"
                                inputClass="w-36"
                                :invalid="!!firstError"
                                @keydown.enter="submit"
                            />
                            <span v-if="row.unit || row.currency" class="text-sm text-gray-500">{{ row.unit ?? row.currency }}</span>
                        </div>
                    </template>
                    <Textarea v-else v-model="naReason" rows="2" autoResize class="w-full text-sm" :placeholder="ctrans('Why does this not apply?')" :invalid="!!firstError" />

                    <p v-if="firstError" class="text-xs text-red-600">{{ firstError }}</p>

                    <div class="flex flex-wrap items-center gap-2">
                        <Button
                            type="primary"
                            size="xs"
                            :icon="mode === 'edit' ? 'fal fa-save' : 'fal fa-ban'"
                            :label="mode === 'edit' ? ctrans('Save') : ctrans('Mark not applicable')"
                            :loading="saving"
                            :disabled="!canSubmit"
                            class="disabled:cursor-not-allowed disabled:opacity-50"
                            @click="submit"
                        />
                        <Button v-if="mode === 'edit'" type="tertiary" size="xs" icon="fal fa-ban" :label="ctrans('Not applicable')" @click="startNotApplicable" />
                        <Button type="negative" size="xs" :label="ctrans('Cancel')" @click="mode = 'view'" />
                    </div>
                </div>
            </div>

            <div v-if="mode === 'view' && row.can_edit" class="flex shrink-0 items-center gap-1">
                <Button v-if="row.can_reopen && row.state !== 'missing'" type="tertiary" size="xs" icon="fal fa-undo" :label="ctrans('Reopen')" :loading="saving" @click="reopen" />
                <Button
                    v-if="!row.needs_supplier_product"
                    :type="row.state === 'missing' ? 'primary' : 'tertiary'"
                    size="xs"
                    icon="fal fa-pencil"
                    :label="row.state === 'missing' ? ctrans('Fill in') : ctrans('Edit')"
                    @click="startEdit"
                />
                <Button v-else-if="row.state === 'missing'" type="tertiary" size="xs" icon="fal fa-ban" :label="ctrans('Not applicable')" @click="startNotApplicable" />
            </div>
        </div>
    </li>
</template>
