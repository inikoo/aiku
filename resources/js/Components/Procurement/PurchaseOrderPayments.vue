<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 12 Oct 2026 06:00:00 Central European Summer Time, Mijas, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed } from "vue"
import { useLocaleStore } from "@/Stores/locale"
import { ctrans } from "@/Composables/useTrans"
import type { PurchaseOrderPaymentsData } from "@/Components/Procurement/PurchaseOrderDepositPayments.vue"

const props = defineProps<{
	payments: PurchaseOrderPaymentsData
	orgCurrencyCode?: string | null
	orgRate?: number | null
}>()

const locale = useLocaleStore()

const money = (amount: number) => locale.currencyFormat(props.payments.currency_code ?? "", amount)
const orgMoney = (amount: number) => (props.orgRate && props.orgCurrencyCode && amount ? locale.currencyFormat(props.orgCurrencyCode, amount * props.orgRate) : null)

const rows = computed(() => [
	{ label: ctrans("Deposits paid"), amount: props.payments.paid },
	{ label: ctrans("Left to pay"), amount: props.payments.left },
])
</script>

<template>
	<div class="mt-2 border-t border-gray-300 pr-2 pt-2 text-gray-500">
		<div class="flex flex-col gap-y-2">
			<div v-for="row in rows" :key="row.label" class="flex items-center justify-between gap-x-3">
				<span class="leading-none">{{ row.label }}</span>
				<span class="flex min-w-20 shrink-0 flex-col text-right tabular-nums">
					<span class="font-medium">{{ money(row.amount) }}</span>
					<span v-if="orgMoney(row.amount)" class="text-xs text-gray-400">{{ orgMoney(row.amount) }}</span>
				</span>
			</div>
		</div>
	</div>
</template>
