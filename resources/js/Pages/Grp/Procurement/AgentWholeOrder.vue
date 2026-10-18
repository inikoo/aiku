<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 16 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, reactive, ref } from "vue"
import { Head, Link, router } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"
import Dialog from "primevue/dialog"
import InputNumber from "primevue/inputnumber"
import InputText from "primevue/inputtext"
import Textarea from "primevue/textarea"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faBoxes, faCheck, faFilePdf, faHandHoldingUsd, faTimes } from "@fal"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Icon from "@/Components/Icon.vue"
import TablePurchaseOrders from "@/Components/Tables/Grp/Org/Procurement/TablePurchaseOrders.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"

library.add(faBoxes, faCheck, faFilePdf, faHandHoldingUsd, faTimes)

interface Money {
	currency_code: string
	amount: number
}

interface SupplierOrder {
	id: number
	reference: string
	supplier_code: string
	supplier_name: string
	state_label: string
	currency_code: string
	cost_total: number
	deposits: number
	requested: number
}

const props = defineProps<{
	title: string
	breadcrumbs?: object[]
	pageHead: PageHeadingTypes
	whole_order: {
		reference: string
		client: string
		state: { value: string; label: string; icon: any }
		number_supplier_orders: number
		number_to_confirm: number
		totals: Money[]
		deposits: Money[]
		requested: Money[]
		paid_to_suppliers: Money[]
		to_pay: Money[]
		pdf_route: routeType
		confirm_route: routeType | null
		deposits_route: routeType | null
		deposit_requests_route: routeType
	}
	supplier_orders: SupplierOrder[]
	data: object
}>()

const locale = useLocaleStore()

const money = (code: string, amount: number | string | null) => locale.currencyFormat(code, Number(amount) || 0)

const moneyList = (amounts: Money[]) => amounts.filter((amount) => amount.amount > 0).map((amount) => money(amount.currency_code, amount.amount)).join(" · ") || "-"

const onError = (errors: Record<string, string>) =>
	notify({ title: ctrans("Something went wrong"), text: Object.values(errors ?? {})[0], type: "error" })

const isConfirmOpen = ref(false)
const confirming = ref(false)

const confirmAll = () => {
	const confirmRoute = props.whole_order.confirm_route
	if (!confirmRoute) return

	router.patch(route(confirmRoute.name, confirmRoute.parameters), {}, {
		preserveScroll: true,
		onStart: () => (confirming.value = true),
		onFinish: () => (confirming.value = false),
		onSuccess: () => {
			isConfirmOpen.value = false
			notify({ title: ctrans("Supplier orders confirmed"), type: "success" })
		},
		onError,
	})
}

const isDepositsOpen = ref(false)
const requesting = ref(false)
const amounts = reactive<Record<number, number | null>>({})
const depositReference = ref("")
const depositNotes = ref("")

const filledDeposits = computed(() =>
	props.supplier_orders
		.filter((supplierOrder) => Number(amounts[supplierOrder.id]) > 0)
		.map((supplierOrder) => ({ purchase_order_id: supplierOrder.id, amount: Number(amounts[supplierOrder.id]) }))
)

const toRequest = computed(() => {
	const totals: Record<string, number> = {}
	props.supplier_orders.forEach((supplierOrder) => {
		const amount = Number(amounts[supplierOrder.id]) || 0
		totals[supplierOrder.currency_code] = (totals[supplierOrder.currency_code] ?? 0) + amount
	})

	return Object.entries(totals).map(([currency_code, amount]) => ({ currency_code, amount }))
})

const openDeposits = () => {
	props.supplier_orders.forEach((supplierOrder) => (amounts[supplierOrder.id] = null))
	depositReference.value = ""
	depositNotes.value = ""
	isDepositsOpen.value = true
}

const requestDeposits = () => {
	const depositsRoute = props.whole_order.deposits_route
	if (!depositsRoute || !filledDeposits.value.length) return

	router.post(
		route(depositsRoute.name, depositsRoute.parameters),
		{ reference: depositReference.value || null, notes: depositNotes.value || null, deposits: filledDeposits.value },
		{
			preserveScroll: true,
			onStart: () => (requesting.value = true),
			onFinish: () => (requesting.value = false),
			onSuccess: () => {
				isDepositsOpen.value = false
				notify({ title: ctrans("Deposits requested from :client", { client: props.whole_order.client }), type: "success" })
			},
			onError,
		}
	)
}
</script>

<template>
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead">
		<template #other>
			<a :href="route(whole_order.pdf_route.name, whole_order.pdf_route.parameters)" target="_blank" rel="noopener">
				<Button type="tertiary" :label="ctrans('Full order PDF')">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-file-pdf" class="text-gray-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</a>
			<Button
				v-if="whole_order.confirm_route"
				type="tertiary"
				:label="ctrans('Confirm all (:count)', { count: whole_order.number_to_confirm })"
				@click="isConfirmOpen = true"
			>
				<template #icon>
					<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
				</template>
			</Button>
			<Button v-if="whole_order.deposits_route" type="tertiary" :label="ctrans('Request deposits')" @click="openDeposits">
				<template #icon>
					<FontAwesomeIcon icon="fal fa-hand-holding-usd" class="text-gray-500" fixed-width aria-hidden="true" />
				</template>
			</Button>
		</template>
	</PageHeading>

	<div class="mx-4 mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
		<div class="rounded-lg border border-gray-200 bg-white p-4">
			<div class="text-xs text-gray-400">{{ ctrans("Client") }}</div>
			<div class="mt-1 font-semibold text-gray-700">{{ whole_order.client }}</div>
			<div class="mt-1 flex items-center gap-1 text-sm text-gray-600">
				<Icon :data="whole_order.state.icon" />
				{{ whole_order.state.label }}
			</div>
		</div>
		<div class="rounded-lg border border-gray-200 bg-white p-4">
			<div class="text-xs text-gray-400">{{ ctrans("Supplier orders") }}</div>
			<div class="mt-1 text-lg font-semibold text-gray-700">{{ locale.number(whole_order.number_supplier_orders) }}</div>
			<div class="mt-1 text-sm text-gray-500">
				{{ ctrans(":count to confirm", { count: whole_order.number_to_confirm }) }}
			</div>
		</div>
		<div class="rounded-lg border border-gray-200 bg-white p-4">
			<div class="text-xs text-gray-400">{{ ctrans("Total") }}</div>
			<div class="mt-1 text-lg font-semibold text-gray-700">{{ moneyList(whole_order.totals) }}</div>
			<div class="mt-1 text-sm text-gray-500">
				{{ ctrans("Paid to suppliers") }}: {{ moneyList(whole_order.paid_to_suppliers) }}
			</div>
			<div class="text-sm text-gray-500">
				{{ ctrans("Still to pay") }}: {{ moneyList(whole_order.to_pay) }}
			</div>
		</div>
		<div class="rounded-lg border border-gray-200 bg-white p-4">
			<div class="text-xs text-gray-400">{{ ctrans("Deposits requested") }}</div>
			<div class="mt-1 text-lg font-semibold text-gray-700">{{ moneyList(whole_order.requested) }}</div>
			<Link
				:href="route(whole_order.deposit_requests_route.name, whole_order.deposit_requests_route.parameters)"
				class="mt-1 inline-block text-sm text-gray-500 underline"
			>
				{{ ctrans("Deposit requests") }}
			</Link>
		</div>
	</div>

	<div class="mt-4">
		<TablePurchaseOrders :data="data" />
	</div>

	<Dialog
		v-model:visible="isConfirmOpen"
		modal
		:header="ctrans('Confirm :reference', { reference: whole_order.reference })"
		:style="{ width: '28rem', maxWidth: 'calc(100vw - 2rem)' }"
	>
		<p class="text-sm text-gray-600">
			{{
				ctrans(
					"Confirms the :count supplier orders still waiting, once the suppliers said they can supply them. It only records their answer here, nothing is sent to the suppliers.",
					{ count: whole_order.number_to_confirm }
				)
			}}
		</p>
		<div class="mt-5 flex justify-end gap-2">
			<Button type="tertiary" :label="ctrans('Cancel')" @click="isConfirmOpen = false">
				<template #icon>
					<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
				</template>
			</Button>
			<Button type="tertiary" :label="ctrans('Confirm all')" :loading="confirming" @click="confirmAll">
				<template #icon>
					<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
				</template>
			</Button>
		</div>
	</Dialog>

	<Dialog
		v-model:visible="isDepositsOpen"
		modal
		:header="ctrans('Request deposits for :reference', { reference: whole_order.reference })"
		:style="{ width: '48rem', maxWidth: 'calc(100vw - 2rem)' }"
	>
		<p class="text-sm text-gray-600">
			{{ ctrans("Type the deposit each supplier asks for. :client gets one request with every amount, and pays you what it owes.", { client: whole_order.client }) }}
		</p>

		<div class="mt-4 overflow-x-auto">
			<table class="min-w-full text-sm">
				<thead class="text-left text-xs uppercase tracking-wide text-gray-400">
					<tr>
						<th class="py-2 pr-4 font-medium">{{ ctrans("Supplier") }}</th>
						<th class="py-2 pr-4 font-medium">{{ ctrans("State") }}</th>
						<th class="py-2 pr-4 text-right font-medium">{{ ctrans("Total") }}</th>
						<th class="py-2 pr-4 text-right font-medium">{{ ctrans("Already requested") }}</th>
						<th class="py-2 text-right font-medium">{{ ctrans("Deposit") }}</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-gray-100">
					<tr v-for="supplierOrder in supplier_orders" :key="supplierOrder.id">
						<td class="py-2 pr-4">
							<span class="font-medium text-gray-700">{{ supplierOrder.supplier_code }}</span>
							<span class="ml-2 text-xs text-gray-400">{{ supplierOrder.supplier_name }}</span>
						</td>
						<td class="py-2 pr-4 text-gray-500">{{ supplierOrder.state_label }}</td>
						<td class="whitespace-nowrap py-2 pr-4 text-right text-gray-600">
							{{ money(supplierOrder.currency_code, supplierOrder.cost_total) }}
						</td>
						<td class="whitespace-nowrap py-2 pr-4 text-right text-gray-500">
							{{ supplierOrder.requested > 0 ? money(supplierOrder.currency_code, supplierOrder.requested) : "-" }}
						</td>
						<td class="py-2 text-right">
							<InputNumber
								v-model="amounts[supplierOrder.id]"
								:min="0"
								:maxFractionDigits="2"
								:placeholder="supplierOrder.currency_code"
								inputClass="w-32 text-right"
							/>
						</td>
					</tr>
				</tbody>
				<tfoot class="border-t border-gray-200">
					<tr>
						<td colspan="4" class="py-2 pr-4 text-right text-xs uppercase tracking-wide text-gray-400">{{ ctrans("To request") }}</td>
						<td class="whitespace-nowrap py-2 text-right font-semibold text-gray-700">{{ moneyList(toRequest) }}</td>
					</tr>
				</tfoot>
			</table>
		</div>

		<div class="mt-4 grid gap-3 sm:grid-cols-2">
			<label class="flex flex-col gap-1 text-sm text-gray-600">
				{{ ctrans("Reference") }}
				<InputText v-model="depositReference" :placeholder="whole_order.reference" />
			</label>
			<label class="flex flex-col gap-1 text-sm text-gray-600">
				{{ ctrans("Notes") }}
				<Textarea v-model="depositNotes" rows="1" autoResize />
			</label>
		</div>

		<div class="mt-5 flex justify-end gap-2">
			<Button type="tertiary" :label="ctrans('Cancel')" @click="isDepositsOpen = false">
				<template #icon>
					<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
				</template>
			</Button>
			<Button
				type="tertiary"
				:label="ctrans('Request deposits')"
				:loading="requesting"
				:disabled="!filledDeposits.length"
				@click="requestDeposits"
			>
				<template #icon>
					<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
				</template>
			</Button>
		</div>
	</Dialog>
</template>
