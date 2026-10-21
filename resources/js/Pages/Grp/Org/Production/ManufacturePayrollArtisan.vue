<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Oct 2026 22:00:00 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Table from "@/Components/Table/Table.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faMoneyCheckAlt, faArrowLeft } from "@fal"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { ctrans } from "@/Composables/useTrans"
import { capitalize } from "@/Composables/capitalize"
import { useFormatTime } from "@/Composables/useFormatTime"
import { PageHeadingTypes } from "@/types/PageHeading"

library.add(faMoneyCheckAlt, faArrowLeft)

defineProps<{
    title: string
    pageHead: PageHeadingTypes
    period: { from: string, to: string }
    summary: Record<string, number> | null
    pay_columns: Record<string, string>
    back_route: { name: string, parameters: object }
    data: object
}>()

const money = (amount: number) => `£${amount.toFixed(2)}`
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #other>
            <Link :href="route(back_route.name, back_route.parameters)">
                <Button type="tertiary" :label="ctrans('Payroll')">
                    <template #icon><FontAwesomeIcon :icon="['fal', 'arrow-left']" fixed-width aria-hidden="true" /></template>
                </Button>
            </Link>
        </template>
    </PageHeading>

    <div class="mx-4 mt-4 text-sm text-gray-500">{{ useFormatTime(period.from) }} – {{ useFormatTime(period.to) }}</div>

    <table v-if="summary" class="mx-4 mt-2 text-sm tabular-nums">
        <thead class="whitespace-nowrap text-xs text-gray-500">
            <tr>
                <th class="pr-6 text-left font-medium" />
                <th v-for="(label, key) in pay_columns" :key="key" class="px-3 text-right font-medium">{{ label }}</th>
                <th class="pl-3 text-right font-medium">{{ ctrans('Total') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="pr-6 text-gray-500">{{ ctrans('Hours') }}</td>
                <td v-for="(label, key) in pay_columns" :key="key" class="px-3 text-right text-gray-500">{{ summary[`${key}_hours`].toFixed(2) }}</td>
                <td />
            </tr>
            <tr>
                <td class="pr-6 text-gray-500">{{ ctrans('Amount') }}</td>
                <td v-for="(label, key) in pay_columns" :key="key" class="px-3 text-right">{{ money(summary[`${key}_amount`]) }}</td>
                <td class="pl-3 text-right font-semibold">{{ money(summary.total) }}</td>
            </tr>
        </tbody>
    </table>

    <Table :resource="data" class="mt-4">
        <template #cell(date)="{ item }">
            <span class="whitespace-nowrap">{{ useFormatTime(item.date) }}</span>
        </template>
        <template #cell(job_order_reference)="{ item }">
            <span class="whitespace-nowrap">{{ item.job_order_reference ?? '—' }}</span>
        </template>
        <template #cell(artefact_code)="{ item }">
            <span class="whitespace-nowrap">{{ item.artefact_code ?? '—' }}</span>
        </template>
        <template #cell(step)="{ item }">
            {{ item.step }}
            <span v-if="item.excluded" class="ml-1 rounded bg-amber-100 px-1.5 py-0.5 text-xs font-medium text-amber-800">{{ ctrans('Left running after shift: not counted until checked') }}</span>
        </template>
        <template #cell(hours)="{ item }">
            <span :class="item.excluded ? 'text-gray-400 line-through' : ''">{{ item.hours.toFixed(2) }}</span>
        </template>
        <template #cell(units)="{ item }">{{ item.units }}</template>
        <template #cell(band_rate)="{ item }">
            {{ money(item.band_rate) }}<span v-if="item.guru_rate" class="text-gray-400"> + {{ money(item.guru_rate) }}</span>
        </template>
        <template #cell(amount)="{ item }">
            <span :class="item.excluded ? 'text-gray-400 line-through' : ''">{{ money(item.amount) }}</span>
        </template>
        <template #footerRows-cell(hours)="{ item }">
            <span class="font-semibold">{{ item.hours?.toFixed(2) }}</span>
        </template>
        <template #footerRows-cell(units)><span /></template>
        <template #footerRows-cell(band_rate)><span /></template>
        <template #footerRows-cell(amount)="{ item }">
            <span class="font-semibold">{{ item.amount !== undefined ? money(item.amount) : '' }}</span>
        </template>
    </Table>
</template>
