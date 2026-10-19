<script setup lang="ts">
import { ref } from "vue"
import { Head, router } from "@inertiajs/vue3"
import InputText from "primevue/inputtext"
import Button from "@/Components/Elements/Buttons/Button.vue"
import SalesDashboard from "@/Components/Procurement/SalesDashboard.vue"
import type { Valuation } from "@/Components/Procurement/AgentSalesValuation.vue"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faLock, faEnvelope } from "@fal"

library.add(faLock, faEnvelope)

defineOptions({ layout: (_h: unknown, page: unknown) => page })

interface PurchaseOrderRow {
	reference: string
	organisation: string
	state: string
	state_label: string
	delivery_label: string
	submitted_at: string | null
	confirmed_at: string | null
	expected_at: string | null
	received_at: string | null
	amount: number
	currency: string | null
	lines: number
}

const props = defineProps<{
	title: string
	supplier: { code: string; name: string }
	group: string
	routes: { send_code: string; verify: string }
	needs_code: boolean
	email?: string | null
	code_sent?: boolean
	valuation?: Valuation
	purchase_orders?: PurchaseOrderRow[]
	errors?: Record<string, string>
	sales?: InstanceType<typeof SalesDashboard>["$props"]["sales"]
	feedback?: InstanceType<typeof SalesDashboard>["$props"]["feedback"]
}>()

const code = ref("")
const isSending = ref(false)
const isChecking = ref(false)

const sendCode = () => {
	isSending.value = true
	router.post(props.routes.send_code, {}, { preserveScroll: true, onFinish: () => (isSending.value = false) })
}

const verify = () => {
	isChecking.value = true
	router.post(props.routes.verify, { code: code.value }, { onFinish: () => (isChecking.value = false) })
}

const money = (amount: number, currency: string | null) =>
	currency ? new Intl.NumberFormat(undefined, { style: "currency", currency, maximumFractionDigits: 0 }).format(Math.round(amount)) : Math.round(amount).toString()
const day = (date: string | null) => (date ? useFormatTime(date) : "—")
</script>

<template>
	<Head :title="title" />
	<div class="min-h-[100dvh] bg-gray-50">
		<header class="border-b border-gray-200 bg-white px-4 py-3">
			<div class="mx-auto max-w-7xl">
				<h1 class="text-lg font-semibold text-gray-800">{{ supplier.name }}</h1>
				<p class="text-xs text-gray-500">{{ ctrans("How your products sell with :group, and your purchase orders", { group }) }}</p>
			</div>
		</header>

		<main class="mx-auto max-w-7xl space-y-4 p-4">
			<section v-if="needs_code" class="mx-auto mt-10 max-w-md space-y-3 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
				<h2 class="flex items-center gap-2 text-sm font-semibold text-gray-700">
					<FontAwesomeIcon icon="fal fa-lock" fixed-width aria-hidden="true" />
					{{ ctrans("Confirm it is you") }}
				</h2>
				<p class="text-sm text-gray-600">{{ ctrans("We send a code to :email. Enter it here to see the page.", { email: email ?? "" }) }}</p>
				<Button type="tertiary" size="xs" :label="code_sent ? ctrans('Send a new code') : ctrans('Send code')" :loading="isSending" @click="sendCode">
					<template #icon><FontAwesomeIcon icon="fal fa-envelope" fixed-width aria-hidden="true" /></template>
				</Button>
				<p v-if="code_sent" class="text-xs text-green-700">{{ ctrans("Code sent. It works for 10 minutes.") }}</p>
				<form class="flex items-center gap-2" @submit.prevent="verify">
					<InputText v-model="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" class="w-36 text-center font-mono tracking-widest" :placeholder="ctrans('Code')" />
					<Button type="tertiary" size="xs" :label="ctrans('Open')" :loading="isChecking" :disabled="code.length < 6" @click="verify" />
				</form>
				<p v-if="errors?.code" class="text-xs text-red-600">{{ errors.code }}</p>
			</section>

			<template v-else>
				<SalesDashboard v-if="valuation" seller="supplier" :staff="false" :links="null" :valuation="valuation" :sales="sales" :feedback="feedback" />

				<section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
					<h2 class="text-sm font-semibold text-gray-700">{{ ctrans("Your purchase orders") }}</h2>
					<p class="mt-0.5 text-xs text-gray-500">{{ ctrans("Orders sent to you and not closed yet, and the ones received in the last 60 days") }}</p>
					<p v-if="!purchase_orders?.length" class="py-6 text-center text-xs text-gray-400">{{ ctrans("No open purchase orders") }}</p>
					<div v-else class="mt-3 overflow-x-auto">
						<table class="w-full text-sm">
							<thead class="text-xs uppercase tracking-wide text-gray-400">
								<tr>
									<th class="whitespace-nowrap pb-1 text-left font-medium">{{ ctrans("Order") }}</th>
									<th class="whitespace-nowrap pb-1 text-left font-medium">{{ ctrans("Ordered by") }}</th>
									<th class="whitespace-nowrap pb-1 text-left font-medium">{{ ctrans("Status") }}</th>
									<th class="whitespace-nowrap pb-1 text-left font-medium">{{ ctrans("Sent") }}</th>
									<th class="whitespace-nowrap pb-1 text-left font-medium">{{ ctrans("Confirmed") }}</th>
									<th class="whitespace-nowrap pb-1 text-left font-medium">{{ ctrans("Expected") }}</th>
									<th class="whitespace-nowrap pb-1 text-left font-medium">{{ ctrans("Received") }}</th>
									<th class="whitespace-nowrap pb-1 text-right font-medium">{{ ctrans("Lines") }}</th>
									<th class="whitespace-nowrap pb-1 text-right font-medium">{{ ctrans("Amount") }}</th>
								</tr>
							</thead>
							<tbody class="divide-y divide-gray-100 tabular-nums text-gray-700">
								<tr v-for="order in purchase_orders" :key="order.reference">
									<td class="whitespace-nowrap py-1.5 font-medium text-gray-800">{{ order.reference }}</td>
									<td class="whitespace-nowrap py-1.5">{{ order.organisation }}</td>
									<td class="whitespace-nowrap py-1.5">
										{{ order.state_label }}
										<span v-if="order.state !== 'settled'" class="text-xs text-gray-400"> · {{ order.delivery_label }}</span>
									</td>
									<td class="whitespace-nowrap py-1.5">{{ day(order.submitted_at) }}</td>
									<td class="whitespace-nowrap py-1.5">{{ day(order.confirmed_at) }}</td>
									<td class="whitespace-nowrap py-1.5">{{ day(order.expected_at) }}</td>
									<td class="whitespace-nowrap py-1.5">{{ day(order.received_at) }}</td>
									<td class="whitespace-nowrap py-1.5 text-right">{{ order.lines }}</td>
									<td class="whitespace-nowrap py-1.5 text-right">{{ money(order.amount, order.currency) }}</td>
								</tr>
							</tbody>
						</table>
					</div>
				</section>
			</template>
		</main>
	</div>
</template>
