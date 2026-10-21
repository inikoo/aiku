<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { routeType } from "@/types/route"

const locale = useLocaleStore()

const props = defineProps<{
    summary: {
        period_label: string
        between: string
        number_invoices: number
        others: number
        countries: {
            country_code: string | null
            country_name: string | null
            number_invoices: number
            share: number
        }[]
    }
    tableRoute: routeType
}>()

const listRoute = () => route(props.tableRoute.name, { ...props.tableRoute.parameters, between: { date: props.summary.between } })
const countryRoute = (countryCode: string) =>
    route("grp.org.accounting.invoices_by_country.show.invoices.index", { ...props.tableRoute.parameters, country: countryCode, between: { date: props.summary.between } })
</script>

<template>
    <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center justify-between gap-3">
            <Link :href="listRoute()" class="font-medium text-gray-700 hover:underline">{{ ctrans("Invoices by country") }}</Link>
            <div class="flex items-center gap-3">
                <div class="text-xs text-gray-500">{{ summary.period_label.toLowerCase() }} · {{ locale.number(summary.number_invoices) }}</div>
                <div v-if="$slots.actions" class="mini-actions">
                    <slot name="actions" />
                </div>
            </div>
        </div>

        <div v-if="!summary.countries.length" class="text-sm text-gray-400">{{ ctrans("No invoices yet") }}</div>

        <table v-else class="w-full text-sm tabular-nums">
            <tbody>
                <tr v-for="row in summary.countries" :key="row.country_code ?? 'unknown'" class="hover:bg-gray-50">
                    <td class="max-w-52 truncate whitespace-nowrap py-0.5 pr-3">
                        <Link v-if="row.country_code" :href="countryRoute(row.country_code)" class="text-gray-700 hover:underline">{{ row.country_name }}</Link>
                        <span v-else class="text-gray-400">{{ ctrans("Unknown") }}</span>
                    </td>
                    <td class="w-full py-0.5 pr-3">
                        <div class="h-1.5 rounded bg-gray-100">
                            <div class="h-1.5 rounded bg-gray-400" :style="{ width: row.share + '%' }" />
                        </div>
                    </td>
                    <td class="whitespace-nowrap py-0.5 pr-3 text-right text-gray-600">{{ row.share }}%</td>
                    <td class="whitespace-nowrap py-0.5 text-right text-xs text-gray-400">{{ locale.number(row.number_invoices) }}</td>
                </tr>
            </tbody>
        </table>

        <Link v-if="summary.others" :href="listRoute()" class="mt-1 block text-xs text-gray-400 hover:underline">{{ ctrans("+:n more", { n: summary.others }) }}</Link>
    </section>
</template>

<style scoped>
.mini-actions :deep(.p-togglebutton) {
    font-size: 0.6875rem;
    line-height: 1rem;
}
.mini-actions :deep(.p-togglebutton-content) {
    padding: 0.125rem 0.5rem;
}
</style>
