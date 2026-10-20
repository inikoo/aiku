<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 07 Sep 2026 12:00:00 Central European Summer Time, Mijas, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3"
import { computed, ref } from "vue"
import DatePicker from "primevue/datepicker"
import Checkbox from "primevue/checkbox"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faMoneyCheckAlt, faDownload } from "@fal"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { ctrans } from "@/Composables/useTrans"
import { capitalize } from "@/Composables/capitalize"
import { PageHeadingTypes } from "@/types/PageHeading"

library.add(faMoneyCheckAlt, faDownload)

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    payroll_export_route: { name: string, parameters: object }
}>()

const toLocalIsoDate = (date: Date): string => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`
const lastMonday = (() => {
    const date = new Date()
    date.setDate(date.getDate() - (date.getDay() + 6) % 7 - 7)
    return date
})()
const payrollFrom = ref<Date | null>(lastMonday)
const payrollTo = ref<Date | null>(new Date(lastMonday.getFullYear(), lastMonday.getMonth(), lastMonday.getDate() + 6))
const timeEditedOnly = ref(false)
const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"

const payrollExportUrl = computed(() => route(props.payroll_export_route.name, {
    ...props.payroll_export_route.parameters,
    from: payrollFrom.value ? toLocalIsoDate(payrollFrom.value) : undefined,
    to: payrollTo.value ? toLocalIsoDate(payrollTo.value) : undefined,
    time_edited: timeEditedOnly.value ? 1 : undefined,
}))
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <div class="mx-4 mt-6 max-w-2xl rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs text-gray-500 mb-1">{{ ctrans('Payroll from') }}</label>
            <DatePicker v-model="payrollFrom" :maxDate="payrollTo ?? undefined" dateFormat="d M yy" :manualInput="false" showIcon iconDisplay="input" :class="fieldFocusClass" :aria-label="ctrans('Payroll from')" />
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">{{ ctrans('To') }}</label>
            <DatePicker v-model="payrollTo" :minDate="payrollFrom ?? undefined" dateFormat="d M yy" :manualInput="false" showIcon iconDisplay="input" :class="fieldFocusClass" :aria-label="ctrans('To')" />
        </div>
        <label class="flex items-center gap-2 pb-2 text-sm text-gray-600">
            <Checkbox v-model="timeEditedOnly" binary />
            {{ ctrans('Edited times only') }}
        </label>
        <a :href="payrollExportUrl">
            <Button type="tertiary" :label="ctrans('Export payroll CSV')">
                <template #icon><FontAwesomeIcon :icon="['fal', 'download']" fixed-width aria-hidden="true" /></template>
            </Button>
        </a>
    </div>
</template>
