<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { routeType } from "@/types/route"
import PaymentMethodBadge from "@/Components/Accounting/PaymentMethodBadge.vue"
import { inject } from "vue"
import { layoutStructure } from "@/Composables/useLayoutStructure"

const locale = useLocaleStore()
const layout = inject("layout", layoutStructure)

defineProps<{
    summary: {
        currency_code: string
        period_label: string
        total_sales: number
        others: number
        methods: {
            method: string
            method_label: string
            payment_account_type: string
            number_payments: number
            total_sales: number
            share: number
        }[]
    }
    tableRoute: routeType
}>()
</script>

<template>
    <Link :href="route(tableRoute.name, tableRoute.parameters)"
        class="block rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:border-gray-400 transition-colors">
        <div class="flex items-center justify-between gap-3 mb-3">
            <div class="font-medium text-gray-700">{{ ctrans("Payment methods") }}</div>
            <div class="flex items-center gap-3">
                <div class="text-xs text-gray-500">{{ summary.period_label.toLowerCase() }} · {{ locale.currencyFormat(summary.currency_code, summary.total_sales) }}</div>
                <div v-if="$slots.actions" class="mini-actions" @click.stop.prevent>
                    <slot name="actions" />
                </div>
            </div>
        </div>

        <div v-if="!summary.methods.length" class="text-sm text-gray-400">{{ ctrans("No payments yet") }}</div>

        <table v-else class="w-full text-sm tabular-nums">
            <tbody>
                <tr v-for="row in summary.methods" :key="row.method + row.payment_account_type">
                    <td class="max-w-52 truncate py-0.5 pr-3">
                        <PaymentMethodBadge :label="row.method_label" :method="row.method" :accountType="row.payment_account_type" />
                    </td>
                    <td class="w-full py-0.5 pr-3">
                        <div class="h-1.5 rounded bg-gray-100">
                            <div class="h-1.5 rounded" :style="{ width: row.share + '%', backgroundColor: layout?.app?.theme?.[4] || '#6366f1' }" />
                        </div>
                    </td>
                    <td class="py-0.5 pr-3 text-right text-gray-600 whitespace-nowrap">{{ row.share }}%</td>
                    <td class="py-0.5 text-right text-xs text-gray-400 whitespace-nowrap">{{ row.number_payments.toLocaleString() }}</td>
                </tr>
            </tbody>
        </table>

        <div v-if="summary.others" class="text-xs text-gray-400 mt-1">{{ ctrans("+:n more", { n: summary.others }) }}</div>
    </Link>
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
