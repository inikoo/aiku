<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 07 Sep 2026 12:00:00 Central European Summer Time, Mijas, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3"
import { computed, ref } from "vue"
import DatePicker from "primevue/datepicker"
import Checkbox from "primevue/checkbox"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Table from "@/Components/Table/Table.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faMoneyCheckAlt, faDownload, faFilter, faHatWizard } from "@fal"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { ctrans } from "@/Composables/useTrans"
import { capitalize } from "@/Composables/capitalize"
import { PageHeadingTypes } from "@/types/PageHeading"

library.add(faMoneyCheckAlt, faDownload, faFilter, faHatWizard)

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    period: { from: string, to: string }
    payroll_export_route: { name: string, parameters: object }
    data: object
}>()

const payKeys = ["working", "band_0", "band_1", "band_2", "band_3", "band_d", "band_dg", "guru"]

const fromIsoDate = (iso: string): Date => {
    const [year, month, day] = iso.split("-").map(Number)
    return new Date(year, month - 1, day)
}
const toLocalIsoDate = (date: Date): string => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`
const payrollFrom = ref<Date | null>(fromIsoDate(props.period.from))
const payrollTo = ref<Date | null>(fromIsoDate(props.period.to))
const timeEditedOnly = ref(false)
const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"

const periodQuery = computed(() => ({
    from: payrollFrom.value ? toLocalIsoDate(payrollFrom.value) : undefined,
    to: payrollTo.value ? toLocalIsoDate(payrollTo.value) : undefined,
}))

const payrollExportUrl = computed(() => route(props.payroll_export_route.name, { ...props.payroll_export_route.parameters, ...periodQuery.value }))
const detailedExportUrl = computed(() => route(props.payroll_export_route.name, {
    ...props.payroll_export_route.parameters,
    ...periodQuery.value,
    detailed: 1,
    time_edited: timeEditedOnly.value ? 1 : undefined,
}))

function applyPeriod() {
    router.get(window.location.pathname, periodQuery.value, { preserveScroll: true })
}

const money = (amount: number) => `£${amount.toFixed(2)}`
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <div class="mx-4 mt-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs text-gray-500 mb-1">{{ ctrans('Payroll from') }}</label>
            <DatePicker v-model="payrollFrom" :maxDate="payrollTo ?? undefined" dateFormat="d M yy" :manualInput="false" showIcon iconDisplay="input" :class="fieldFocusClass" :aria-label="ctrans('Payroll from')" />
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">{{ ctrans('To') }}</label>
            <DatePicker v-model="payrollTo" :minDate="payrollFrom ?? undefined" dateFormat="d M yy" :manualInput="false" showIcon iconDisplay="input" :class="fieldFocusClass" :aria-label="ctrans('To')" />
        </div>
        <Button type="tertiary" :label="ctrans('Apply')" @click="applyPeriod">
            <template #icon><FontAwesomeIcon :icon="['fal', 'filter']" fixed-width aria-hidden="true" /></template>
        </Button>
        <a :href="payrollExportUrl">
            <Button type="tertiary" :label="ctrans('Export payroll CSV')">
                <template #icon><FontAwesomeIcon :icon="['fal', 'download']" fixed-width aria-hidden="true" /></template>
            </Button>
        </a>
        <div class="ml-auto flex items-end gap-3">
            <label class="flex items-center gap-2 pb-2 text-sm text-gray-600">
                <Checkbox v-model="timeEditedOnly" binary />
                {{ ctrans('Edited times only') }}
            </label>
            <a :href="detailedExportUrl">
                <Button type="tertiary" :label="ctrans('Export detailed CSV')">
                    <template #icon><FontAwesomeIcon :icon="['fal', 'download']" fixed-width aria-hidden="true" /></template>
                </Button>
            </a>
        </div>
    </div>

    <p class="mx-4 mt-2 text-xs text-gray-500">
        {{ ctrans('Working hours are paid at the National Living Wage (band 0). Each band adds what it pays above that, for its hours. Jobs left running after the end of the shift are not counted until their times are checked.') }}
    </p>

    <Table :resource="data" class="mt-2">
        <template #cell(worker)="{ item }">
            <div class="flex items-center gap-2 whitespace-nowrap">
                <Link :href="route(item.route.name, item.route.parameters)" class="font-medium text-gray-700 hover:underline">{{ item.worker }}</Link>
                <span v-if="item.artisan_pay_type === 'hourly'" class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600">{{ ctrans('Hourly') }}</span>
                <span v-if="item.is_guru" class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600"><FontAwesomeIcon :icon="['fal', 'hat-wizard']" fixed-width aria-hidden="true" /> {{ ctrans('Guru') }}</span>
                <span v-if="item.excluded_sessions" class="rounded bg-amber-100 px-1.5 py-0.5 text-xs font-medium text-amber-800" v-tooltip="ctrans('Left running after the end of the shift: not counted until the times are checked on the Performance page')">
                    {{ ctrans(':count to check', { count: item.excluded_sessions }) }}
                </span>
            </div>
        </template>
        <template v-for="key in payKeys" :key="key" #[`cell(${key})`]="{ item }">
            <div class="grid grid-cols-[3.5rem_4.5rem] justify-end gap-2 tabular-nums whitespace-nowrap">
                <span class="text-right text-gray-400">{{ item[`${key}_hours`].toFixed(2) }}</span>
                <span class="text-right">{{ money(item[`${key}_amount`]) }}</span>
            </div>
        </template>
        <template #cell(total)="{ item }">
            <span class="font-semibold tabular-nums">{{ money(item.total) }}</span>
        </template>

        <template v-for="key in payKeys" :key="`footer-${key}`" #[`footerRows-cell(${key})`]="{ item }">
            <div class="grid grid-cols-[3.5rem_4.5rem] justify-end gap-2 tabular-nums whitespace-nowrap font-semibold text-gray-700">
                <span class="text-right">{{ item[`${key}_hours`].toFixed(2) }}</span>
                <span class="text-right">{{ money(item[`${key}_amount`]) }}</span>
            </div>
        </template>
        <template #footerRows-cell(total)="{ item }">
            <span class="font-semibold tabular-nums text-gray-800">{{ money(item.total) }}</span>
        </template>
    </Table>
</template>
