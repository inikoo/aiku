<script setup lang="ts">
import { ref, computed } from "vue"
import { router } from "@inertiajs/vue3"
import { useConfirm } from "primevue/useconfirm"
import { notify } from "@kyvg/vue3-notification"
import Dialog from "primevue/dialog"
import PaymentDialog, { PaymentPayload } from "@/Components/Procurement/PaymentDialog.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlus, faTrashAlt, faTimes, faCheck, faExclamationTriangle, faHandHoldingUsd, faPaperclip } from "@fal"
import { useLocaleStore } from "@/Stores/locale"
import { useFormatTime } from "@/Composables/useFormatTime"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"

library.add(faPlus, faTrashAlt, faTimes, faCheck, faExclamationTriangle, faHandHoldingUsd, faPaperclip)

interface Payment {
	id: number
	date: string
	amount: number
	reference: string | null
	notes: string | null
	method: string | null
	bank_statement: string | null
	delete_route: routeType
}

const props = defineProps<{
	currency: string
	hasCharges: boolean
	isEstimate: boolean
	agent: {
		can_edit: boolean
		charges_approved: boolean
		approve_route: routeType
		deposits: { type: string; reference: string | null; date: string | null; amount: number }[]
		payments: Payment[]
		methods: { name: string; code: string }[]
		payment_store_route: routeType
	}
}>()

const locale = useLocaleStore()
const confirm = useConfirm()

const money = (amount: number) => locale.currencyFormat(props.currency, amount)

const onError = (errors: Record<string, string>) =>
	notify({ title: ctrans("Something went wrong"), text: Object.values(errors)[0], type: "error" })

const isOpen = ref(false)
const needsApproval = computed(() => props.hasCharges && !props.isEstimate && !props.agent.charges_approved)

const approveLoading = ref(false)
const confirmApproveCharges = () =>
	confirm.require({
		group: "stock-delivery",
		message: ctrans("Approve the agent's charges? The costing can then go ahead."),
		header: ctrans("Approve charges"),
		acceptLabel: ctrans("Approve charges"),
		rejectLabel: ctrans("Cancel"),
		accept: () =>
			router.post(route(props.agent.approve_route.name, props.agent.approve_route.parameters), {}, {
				preserveScroll: true,
				onStart: () => (approveLoading.value = true),
				onFinish: () => (approveLoading.value = false),
				onError,
			}),
	})

const isPaymentDialogOpen = ref(false)
const paymentLoading = ref(false)

const storePayment = (payload: PaymentPayload) =>
	router.post(route(props.agent.payment_store_route.name, props.agent.payment_store_route.parameters), payload, {
		preserveScroll: true,
		forceFormData: true,
		onStart: () => (paymentLoading.value = true),
		onFinish: () => (paymentLoading.value = false),
		onSuccess: () => {
			isPaymentDialogOpen.value = false
			notify({ title: ctrans("Payment recorded"), type: "success" })
		},
		onError,
	})

const confirmDeletePayment = (row: Payment) =>
	confirm.require({
		group: "stock-delivery",
		message: ctrans("Remove the payment of :amount from :date?", { amount: String(money(row.amount)), date: useFormatTime(row.date) }),
		header: ctrans("Remove payment"),
		acceptLabel: ctrans("Remove"),
		rejectLabel: ctrans("Cancel"),
		acceptClass: "p-button-danger",
		accept: () =>
			router.delete(route(row.delete_route.name, row.delete_route.parameters), {
				preserveScroll: true,
				onSuccess: () => notify({ title: ctrans("Payment removed"), type: "success" }),
				onError,
			}),
	})
</script>

<template>
	<div class="mt-3 flex justify-end">
		<Button type="tertiary" size="xs" :label="ctrans('Agent payments')" @click="isOpen = true">
			<template #icon>
				<FontAwesomeIcon :icon="needsApproval ? 'fal fa-exclamation-triangle' : 'fal fa-hand-holding-usd'" :class="needsApproval ? 'text-amber-500' : ''" fixed-width aria-hidden="true" />
			</template>
		</Button>

		<Dialog
			v-model:visible="isOpen"
			modal
			dismissableMask
			:closable="false"
			:style="{ width: '34rem', maxWidth: 'calc(100vw - 2rem)' }"
			:pt="{ header: { class: '!px-4 !py-2.5 border-b border-gray-200' }, content: { class: '!p-4' } }"
		>
			<template #header>
				<div class="flex w-full items-center justify-between gap-3">
					<span class="text-sm font-semibold text-gray-900">{{ ctrans("Agent charges and payments") }}</span>
					<button type="button" class="text-gray-400 hover:text-gray-700" :aria-label="ctrans('Close')" @click="isOpen = false">
						<FontAwesomeIcon icon="fal fa-times" fixed-width aria-hidden="true" />
					</button>
				</div>
			</template>
	<div class="space-y-3 text-sm">
		<div v-if="hasCharges && !isEstimate" class="flex flex-wrap items-center justify-between gap-2">
			<template v-if="!agent.charges_approved">
				<span class="rounded bg-amber-50 px-2 py-1 text-amber-700">{{ ctrans("The agent's charges wait for your approval, the costing waits for it") }}</span>
				<Button v-if="agent.can_edit" type="tertiary" size="xs" :label="ctrans('Approve charges')" :loading="approveLoading" @click="confirmApproveCharges">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</template>
			<span v-else class="rounded bg-green-50 px-2 py-1 text-green-700">{{ ctrans("Charges approved") }}</span>
		</div>

		<div>
			<div class="flex items-center justify-between">
				<h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ ctrans("Payments to the agent") }}</h3>
				<Button v-if="agent.can_edit" type="tertiary" size="xs" :label="ctrans('Add payment')" @click="isPaymentDialogOpen = true">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-plus" class="text-green-600" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>
			<p v-if="!agent.deposits.length && !agent.payments.length" class="mt-2 text-gray-500">{{ ctrans("No deposits applied and no payments recorded yet.") }}</p>
			<ul v-else class="mt-2 space-y-1.5">
				<li v-for="(deposit, index) in agent.deposits" :key="`deposit-${index}`" class="flex items-center justify-between gap-4">
					<span class="min-w-0 truncate text-gray-700">
						<span class="mr-1.5 rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600">{{ ctrans("Deposit") }}</span>
						{{ deposit.reference ?? "—" }}
						<span v-if="deposit.date" class="text-gray-400"> · {{ useFormatTime(deposit.date) }}</span>
					</span>
					<span class="tabular-nums text-gray-800">{{ money(deposit.amount) }}</span>
				</li>
				<li v-for="row in agent.payments" :key="`payment-${row.id}`" class="flex items-center justify-between gap-4">
					<span class="min-w-0 text-gray-700">
						<span class="mr-1.5 rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600">{{ row.method ?? ctrans("Payment") }}</span>
						{{ row.reference ?? "—" }}
						<span class="text-gray-400"> · {{ useFormatTime(row.date) }}</span>
						<a v-if="row.bank_statement" :href="route('grp.media.download', { id: row.bank_statement })" target="_blank" class="ml-2 text-gray-500 underline decoration-gray-300 hover:text-gray-800">
							<FontAwesomeIcon icon="fal fa-paperclip" aria-hidden="true" />
							{{ ctrans("Bank statement") }}
						</a>
						<span v-if="row.notes" class="block truncate text-xs text-gray-500">{{ row.notes }}</span>
					</span>
					<span class="flex items-center gap-2">
						<span class="tabular-nums text-gray-800">{{ money(row.amount) }}</span>
						<button v-if="agent.can_edit" type="button" class="text-gray-400 hover:text-red-600" :title="ctrans('Remove')" :aria-label="ctrans('Remove')" @click="confirmDeletePayment(row)">
							<FontAwesomeIcon icon="fal fa-trash-alt" fixed-width aria-hidden="true" />
						</button>
					</span>
				</li>
			</ul>
		</div>

		<PaymentDialog
			v-model:visible="isPaymentDialogOpen"
			:title="ctrans('Add payment')"
			:currency="currency"
			:methods="agent.methods"
			:loading="paymentLoading"
			withNotes
			@submit="storePayment"
		/>
	</div>
		</Dialog>
	</div>
</template>
