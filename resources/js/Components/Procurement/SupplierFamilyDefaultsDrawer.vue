<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { router } from "@inertiajs/vue3"
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import Drawer from "primevue/drawer"
import InputNumber from "primevue/inputnumber"
import InputText from "primevue/inputtext"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faSave } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import ProductionCalendarStrip from "@/Components/Procurement/ProductionCalendarStrip.vue"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"

library.add(faSave)

export type FamilyDefaults = {
    minimum_carton_order: number | null
    estimated_lead_time_days: number | null
    packaging_terms: Record<string, string | number | null>
    production_calendar: Record<string, string | null>
}

const props = defineProps<{
    supplierCode: string
    family: { code: string | null; name: string | null; defaults: FamilyDefaults; update_route: routeType | null } | null
}>()

const emit = defineEmits<{ (e: "close"): void }>()

const visible = computed({
    get: () => !!props.family,
    set: (value) => {
        if (!value) emit("close")
    },
})

const form = ref<FamilyDefaults>({ minimum_carton_order: null, estimated_lead_time_days: null, packaging_terms: {}, production_calendar: {} })
watch(
    () => props.family,
    (family) => {
        if (family) {
            form.value = JSON.parse(JSON.stringify({ ...family.defaults, packaging_terms: { ...family.defaults.packaging_terms }, production_calendar: { ...family.defaults.production_calendar } }))
        }
    },
    { immediate: true }
)

const payerOptions = computed(() => [
    { value: "supplier", label: ctrans("Supplier pays") },
    { value: "us", label: ctrans("We pay") },
    { value: "not_needed", label: ctrans("Not needed") },
])
const paidTerms = computed(() => [
    { key: "retail_box", label: ctrans("Retail box") },
    { key: "extra_wrapping", label: ctrans("Extra wrapping") },
    { key: "special_packaging", label: ctrans("Special packaging the supplier must supply") },
    { key: "label_application", label: ctrans("Label application") },
])

const brush = ref<string>("best_to_order")
const brushOptions = computed(() => [
    { value: "best_to_order", label: ctrans("Best to order"), tone: "emerald" as const },
    { value: "peak_or_slow", label: ctrans("Peak or slow"), tone: "amber" as const },
    { value: "cannot_produce", label: ctrans("Can't produce"), tone: "red" as const },
    { value: "clear", label: ctrans("Clear"), tone: "gray" as const },
])
const paint = (month: number) => {
    const calendar = { ...form.value.production_calendar }
    if (brush.value === "clear" || calendar[month] === brush.value) {
        delete calendar[month]
    } else {
        calendar[month] = brush.value
    }
    form.value.production_calendar = calendar
}

const isSaving = ref(false)
const save = async () => {
    const updateRoute = props.family?.update_route
    if (!updateRoute || isSaving.value) return

    isSaving.value = true
    try {
        await axios.patch(route(updateRoute.name, updateRoute.parameters), {
            minimum_carton_order: form.value.minimum_carton_order,
            estimated_lead_time_days: form.value.estimated_lead_time_days,
            packaging_terms: Object.fromEntries(Object.entries(form.value.packaging_terms).filter(([, value]) => value !== null && value !== "")),
            production_calendar: Object.fromEntries(Object.entries(form.value.production_calendar).filter(([, value]) => value)),
        })
        emit("close")
        router.reload({ only: ["suppliers"] })
        notify({ title: ctrans("Saved"), text: ctrans("Every product of this family at this supplier takes these unless it sets its own"), type: "success" })
    } catch (error: any) {
        const errors = error?.response?.data?.errors
        notify({ title: ctrans("Something went wrong"), text: errors ? Object.values(errors).flat().join(" ") : ctrans("Could not save"), type: "error" })
    } finally {
        isSaving.value = false
    }
}

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"
</script>

<template>
    <Drawer v-model:visible="visible" position="right" class="!w-full sm:!w-[34rem]"
        :header="family ? `${supplierCode} › ${family.code}` : ''">
        <div v-if="family" class="space-y-5">
            <p class="text-xs text-gray-500">
                {{ ctrans("What holds for :family when bought from :supplier. Its products take these unless they set their own.", { family: family.name ?? family.code ?? "", supplier: supplierCode }) }}
            </p>

            <div class="grid grid-cols-2 gap-3">
                <label class="text-sm text-gray-600">
                    {{ ctrans("MOQ (cartons)") }}
                    <InputNumber v-model="form.minimum_carton_order" :min="1" :useGrouping="false" class="mt-1 block" inputClass="!w-full !py-1.5 !text-sm" :class="fieldFocusClass" />
                </label>
                <label class="text-sm text-gray-600">
                    {{ ctrans("Lead time (days)") }}
                    <InputNumber v-model="form.estimated_lead_time_days" :min="1" :max="365" :useGrouping="false" class="mt-1 block" inputClass="!w-full !py-1.5 !text-sm" :class="fieldFocusClass" />
                </label>
            </div>

            <div>
                <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ ctrans("Production calendar") }}</div>
                <SegmentedToggle v-model="brush" :options="brushOptions" :ariaLabel="ctrans('Mark months as')" />
                <ProductionCalendarStrip :calendar="form.production_calendar" class="mt-2" @paint="paint" />
                <p class="mt-1 text-xs text-gray-400">{{ ctrans("Pick a mark, then click the months it applies to.") }}</p>
            </div>

            <div>
                <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ ctrans("Packaging terms") }}</div>
                <div class="space-y-2">
                    <div v-for="term in paidTerms" :key="term.key" class="flex flex-wrap items-center justify-between gap-2">
                        <span class="text-sm text-gray-600">{{ term.label }}</span>
                        <SegmentedToggle v-model="form.packaging_terms[term.key]" :options="payerOptions" :ariaLabel="term.label" />
                    </div>
                    <label class="flex items-center justify-between gap-2 text-sm text-gray-600">
                        {{ ctrans("Protective materials in the carton") }}
                        <InputText v-model="form.packaging_terms.protective_materials" class="!w-56 !py-1 !text-sm" :class="fieldFocusClass" />
                    </label>
                    <label class="flex items-center justify-between gap-2 text-sm text-gray-600">
                        {{ ctrans("Silica gel sachets per carton") }}
                        <InputNumber v-model="form.packaging_terms.silica_gel_sachets" :min="0" :useGrouping="false" inputClass="!w-20 !py-1 !text-sm" :class="fieldFocusClass" />
                    </label>
                </div>
            </div>

            <div class="flex justify-end">
                <Button type="tertiary" :label="ctrans('Save')" :loading="isSaving" @click="save">
                    <template #icon><FontAwesomeIcon icon="fal fa-save" class="text-green-600" fixed-width aria-hidden="true" /></template>
                </Button>
            </div>
        </div>
    </Drawer>
</template>
