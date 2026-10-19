<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 16 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref } from "vue"
import { Head, Link, router } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"
import Dialog from "primevue/dialog"
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
		requested: Money[]
		paid_to_suppliers: Money[]
		to_pay: Money[]
		pdf_route: routeType
		confirm_route: routeType | null
		money_request_route: routeType | null
		deposit_requests_route: routeType
	}
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
			<Link v-if="whole_order.money_request_route" :href="route(whole_order.money_request_route.name, whole_order.money_request_route.parameters)">
				<Button type="tertiary" :label="ctrans('Request money')">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-hand-holding-usd" class="text-gray-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</Link>
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
			<div class="text-xs text-gray-400">{{ ctrans("Money requested") }}</div>
			<div class="mt-1 text-lg font-semibold text-gray-700">{{ moneyList(whole_order.requested) }}</div>
			<Link
				:href="route(whole_order.deposit_requests_route.name, whole_order.deposit_requests_route.parameters)"
				class="mt-1 inline-block text-sm text-gray-500 underline"
			>
				{{ ctrans("Money requests") }}
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

</template>
