<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faMoneyCheckAlt } from "@fal"
import { useLocaleStore } from "@/Stores/locale"
import { ctrans } from "@/Composables/useTrans"

library.add(faMoneyCheckAlt)

export interface AgentPurchaseOrderMoneyData {
	currency_code: string
	goods: number
	requested: number
	paid_to_suppliers: number
	to_pay: number
	route: { name: string; parameters: Record<string, unknown>; label: string } | null
}

const props = defineProps<{
	money: AgentPurchaseOrderMoneyData
}>()

const locale = useLocaleStore()
const format = (amount: number) => locale.currencyFormat(props.money.currency_code, amount)
</script>

<template>
	<div class="mt-2 space-y-2 border-t border-gray-200 pt-2 text-xs">
		<div class="flex items-baseline justify-between gap-2">
			<span class="text-gray-500">{{ ctrans("Goods") }}</span>
			<span class="tabular-nums text-gray-700">{{ format(money.goods) }}</span>
		</div>
		<div class="flex items-baseline justify-between gap-2">
			<span class="text-gray-500">{{ ctrans("Money requested") }}</span>
			<span class="tabular-nums text-gray-700">{{ format(money.requested) }}</span>
		</div>
		<div class="flex items-baseline justify-between gap-2">
			<span class="text-gray-500">{{ ctrans("Paid to suppliers") }}</span>
			<span class="tabular-nums" :class="money.to_pay <= 0 ? 'text-green-600' : 'text-gray-700'">{{ format(money.paid_to_suppliers) }}</span>
		</div>
		<div class="flex items-baseline justify-between gap-2 font-medium">
			<span class="text-gray-600">{{ ctrans("Still to pay") }}</span>
			<span class="tabular-nums" :class="money.to_pay <= 0 ? 'text-green-600' : 'text-amber-600'">{{ format(money.to_pay) }}</span>
		</div>
		<Link
			v-if="money.route"
			:href="route(money.route.name, money.route.parameters)"
			class="inline-flex items-center gap-1 text-gray-500 underline decoration-gray-300 hover:text-gray-800">
			<FontAwesomeIcon icon="fal fa-money-check-alt" class="text-gray-400" fixed-width aria-hidden="true" />
			{{ money.route.label }}
		</Link>
	</div>
</template>
