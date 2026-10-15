<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 12 Oct 2026 06:00:00 Central European Summer Time, Mijas, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref, computed } from "vue"
import { router } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"
import Dialog from "primevue/dialog"
import DatePicker from "primevue/datepicker"
import InputNumber from "primevue/inputnumber"
import InputText from "primevue/inputtext"
import Select from "primevue/select"
import FileUpload from "primevue/fileupload"
import Textarea from "primevue/textarea"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faBan, faCheck, faCheckCircle, faCoins, faPaperclip, faPencil, faTimes, faUndo } from "@fal"
import { useLocaleStore } from "@/Stores/locale"
import { useFormatTime } from "@/Composables/useFormatTime"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"

library.add(faBan, faCheck, faCheckCircle, faCoins, faPaperclip, faPencil, faTimes, faUndo)

interface Deposit {
	amount: number | null
	paid_at: string | null
	paid_by: string | null
	method: string | null
	reference: string | null
	bank_statements: { ulid: string; caption: string | null }[]
}

const props = defineProps<{
	payments: {
		can_confirm: boolean
		currency_code: string | null
		total: number
		methods: { name: string; code: string }[]
		deposit: Deposit
		balance: number
		confirm_route: routeType
		undo_route: routeType
		deposit_route: routeType
	}
	orgCurrencyCode?: string | null
	orgRate?: number | null
}>()

const locale = useLocaleStore()

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent] focus:!border-[--app-accent]"
const quietLinkClass = "flex items-center gap-1 text-gray-500 underline decoration-gray-300 hover:text-gray-800"
const dialogPt = { header: { class: "!px-4 !py-2.5 border-b border-gray-200" }, content: { class: "!p-4" }, footer: { class: "!px-4 !pb-4" } }
const chooseFilePt = { pcChooseButton: { root: { class: "!border !border-gray-300 !bg-white !text-gray-700 hover:!bg-gray-50" } } }

const money = (amount: number) => locale.currencyFormat(props.payments.currency_code ?? "", amount)
const orgMoney = (amount: number | null) => (props.orgRate && props.orgCurrencyCode && amount ? locale.currencyFormat(props.orgCurrencyCode, amount * props.orgRate) : null)

const deposit = computed(() => props.payments.deposit)
const isDepositDecided = computed(() => deposit.value.amount !== null)
const needsDeposit = computed(() => (deposit.value.amount ?? 0) > 0)

const onError = (errors: Record<string, string>) =>
	notify({ title: ctrans("Something went wrong"), text: Object.values(errors)[0], type: "error" })

const isLoading = ref(false)
const visit = { preserveScroll: true, onStart: () => (isLoading.value = true), onFinish: () => (isLoading.value = false), onError }

const isDepositDialogOpen = ref(false)
const depositAmount = ref<number | null>(null)

const openDepositDialog = () => {
	depositAmount.value = deposit.value.amount || null
	isDepositDialogOpen.value = true
}

const setDeposit = (amount: number | null) =>
	router.patch(route(props.payments.deposit_route.name, props.payments.deposit_route.parameters), { amount }, {
		...visit,
		onSuccess: () => (isDepositDialogOpen.value = false),
	})

const isPayDialogOpen = ref(false)
const form = ref<{ paid_at: Date | null; method: string; reference: string; file: File | null; reason: string }>({ paid_at: new Date(), method: "bank_transfer", reference: "", file: null, reason: "" })

const openPayDialog = () => {
	form.value = { paid_at: new Date(), method: "bank_transfer", reference: "", file: null, reason: "" }
	isPayDialogOpen.value = true
}

const needsStatement = computed(() => form.value.method !== "cash")
const toIsoDate = (date: Date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`
const needsReason = computed(() => !!form.value.paid_at && toIsoDate(form.value.paid_at) < toIsoDate(new Date(Date.now() - 86400000)))
const canSubmit = computed(() => !!form.value.paid_at && !!form.value.method && (!needsStatement.value || !!form.value.file) && (!needsReason.value || !!form.value.reason.trim()))

const confirmPayment = () => {
	if (!canSubmit.value) return
	router.post(
		route(props.payments.confirm_route.name, props.payments.confirm_route.parameters),
		{
			paid_at: toIsoDate(form.value.paid_at as Date),
			method: form.value.method,
			reference: form.value.reference || null,
			bank_statement: form.value.file,
			reason: needsReason.value ? form.value.reason.trim() : null,
		},
		{
			...visit,
			forceFormData: true,
			onSuccess: () => {
				isPayDialogOpen.value = false
				notify({ title: ctrans("Payment confirmed"), type: "success" })
			},
		}
	)
}

const undoPayment = () => router.patch(route(props.payments.undo_route.name, props.payments.undo_route.parameters), {}, visit)
</script>

<template>
	<div class="mt-2 border-t border-gray-300 pr-2 pt-2 text-gray-500">
		<div class="flex flex-col gap-y-2">
			<div class="flex items-center justify-between gap-x-3">
				<div class="flex min-w-0 flex-col">
					<span class="leading-none">{{ ctrans("Deposit") }}</span>
					<span v-if="isDepositDecided" class="mt-1 flex flex-wrap items-center gap-x-2 text-xs text-gray-400">
						<template v-if="deposit.paid_at">
							<span v-tooltip="deposit.paid_by ? ctrans('confirmed by :name', { name: deposit.paid_by }) : undefined" class="flex items-center gap-1">
								<FontAwesomeIcon icon="fal fa-check-circle" class="text-green-600" aria-hidden="true" />
								{{ ctrans("Paid :date", { date: useFormatTime(deposit.paid_at) }) }}
							</span>
							<span v-if="deposit.method || deposit.reference">{{ [deposit.method, deposit.reference].filter(Boolean).join(" · ") }}</span>
							<a
								v-for="statement in deposit.bank_statements"
								:key="statement.ulid"
								v-tooltip="statement.caption"
								:href="route('grp.media.download', { id: statement.ulid })"
								target="_blank"
								:class="quietLinkClass">
								<FontAwesomeIcon icon="fal fa-paperclip" aria-hidden="true" />
								{{ ctrans("Bank statement") }}
							</a>
							<button v-if="payments.can_confirm" type="button" :class="quietLinkClass" @click="undoPayment">
								<FontAwesomeIcon icon="fal fa-undo" class="text-amber-500" aria-hidden="true" />
								{{ ctrans("Undo") }}
							</button>
						</template>
						<template v-else>
							{{ needsDeposit ? ctrans("Not paid yet") : ctrans("No deposit needed") }}
							<button v-if="payments.can_confirm" type="button" :class="quietLinkClass" @click="openDepositDialog">
								<FontAwesomeIcon icon="fal fa-pencil" aria-hidden="true" />
								{{ ctrans("Change") }}
							</button>
						</template>
					</span>
				</div>

				<div class="flex shrink-0 items-center gap-x-3">
					<template v-if="payments.can_confirm && !isDepositDecided">
						<Button type="tertiary" size="xs" :label="ctrans('No deposit needed')" :loading="isLoading" @click="setDeposit(0)">
							<template #icon>
								<FontAwesomeIcon icon="fal fa-ban" class="text-gray-400" fixed-width aria-hidden="true" />
							</template>
						</Button>
						<Button type="tertiary" size="xs" :label="ctrans('Set deposit')" @click="openDepositDialog">
							<template #icon>
								<FontAwesomeIcon icon="fal fa-coins" class="text-amber-500" fixed-width aria-hidden="true" />
							</template>
						</Button>
					</template>
					<Button v-else-if="payments.can_confirm && needsDeposit && !deposit.paid_at" type="tertiary" size="xs" :label="ctrans('Pay deposit')" @click="openPayDialog">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
						</template>
					</Button>
					<span v-if="needsDeposit" class="flex min-w-20 flex-col text-right tabular-nums">
						<span class="font-medium">{{ money(deposit.amount ?? 0) }}</span>
						<span v-if="orgMoney(deposit.amount)" class="text-xs text-gray-400">{{ orgMoney(deposit.amount) }}</span>
					</span>
				</div>
			</div>

			<div class="flex items-center justify-between gap-x-3">
				<div class="flex min-w-0 flex-col">
					<span class="leading-none">{{ needsDeposit ? ctrans("Balance") : ctrans("Payment") }}</span>
					<span class="mt-1 text-xs text-gray-400">{{ ctrans("Paid on the stock delivery, against the supplier's invoice") }}</span>
				</div>
				<span class="flex min-w-20 shrink-0 flex-col text-right tabular-nums">
					<span class="font-medium">{{ money(payments.balance) }}</span>
					<span v-if="orgMoney(payments.balance)" class="text-xs text-gray-400">{{ orgMoney(payments.balance) }}</span>
				</span>
			</div>
		</div>

		<Dialog v-model:visible="isDepositDialogOpen" modal dismissableMask :closable="false" :pt="dialogPt" class="w-full max-w-md">
			<template #header>
				<div class="flex w-full items-center justify-between gap-3">
					<span class="text-sm font-semibold text-gray-900">{{ ctrans("Set deposit") }}</span>
					<button type="button" class="text-gray-400 hover:text-gray-700" :aria-label="ctrans('Close')" @click="isDepositDialogOpen = false">
						<FontAwesomeIcon icon="fal fa-times" fixed-width aria-hidden="true" />
					</button>
				</div>
			</template>
			<div class="block text-sm">
				<span class="text-gray-600">{{ ctrans("Deposit the supplier asks for") }}</span>
				<InputNumber v-model="depositAmount" mode="currency" :currency="payments.currency_code ?? undefined" :min="0" :max="payments.total" class="mt-1 w-full" :class="fieldFocusClass" :pt="{ pcInputText: { root: { class: '!w-full' } } }" />
				<span class="mt-1 flex flex-wrap items-center gap-x-2 text-xs text-gray-400">
					<span v-if="orgMoney(depositAmount)">{{ orgMoney(depositAmount) }} ·</span>
					{{ ctrans("Order total :total", { total: String(money(payments.total)) }) }}
					<button type="button" :class="quietLinkClass" @click="depositAmount = payments.total">{{ ctrans("Pay it all up front") }}</button>
				</span>
			</div>
			<template #footer>
				<Button type="tertiary" :label="ctrans('Cancel')" @click="isDepositDialogOpen = false">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
				<Button v-if="isDepositDecided" type="tertiary" :label="ctrans('No deposit needed')" :loading="isLoading" @click="setDeposit(0)">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-ban" class="text-gray-400" fixed-width aria-hidden="true" />
					</template>
				</Button>
				<Button type="tertiary" :label="ctrans('Set deposit')" :disabled="!depositAmount" :loading="isLoading" @click="setDeposit(depositAmount)">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</template>
		</Dialog>

		<Dialog v-model:visible="isPayDialogOpen" modal dismissableMask :closable="false" :pt="dialogPt" class="w-full max-w-md">
			<template #header>
				<div class="flex w-full items-center justify-between gap-3">
					<span class="text-sm font-semibold text-gray-900">{{ ctrans("Pay deposit") }}</span>
					<button type="button" class="text-gray-400 hover:text-gray-700" :aria-label="ctrans('Close')" @click="isPayDialogOpen = false">
						<FontAwesomeIcon icon="fal fa-times" fixed-width aria-hidden="true" />
					</button>
				</div>
			</template>
			<div class="space-y-3">
				<div class="text-sm">
					<span class="text-gray-600">{{ ctrans("Amount") }}</span>
					<div class="mt-1 tabular-nums text-gray-900">{{ money(deposit.amount ?? 0) }}</div>
					<div v-if="orgMoney(deposit.amount)" class="text-xs tabular-nums text-gray-400">{{ orgMoney(deposit.amount) }}</div>
				</div>
				<label class="block text-sm">
					<span class="text-gray-600">{{ ctrans("Date the payment was sent") }}</span>
					<DatePicker v-model="form.paid_at" dateFormat="dd/mm/yy" :maxDate="new Date()" :manualInput="false" showIcon iconDisplay="input" class="mt-1 w-full" :class="fieldFocusClass" :pt="{ pcInputText: { root: { class: '!w-full' } } }" />
				</label>
				<label v-if="needsReason" class="block text-sm">
					<span class="text-gray-600">{{ ctrans("Reason for the past date (kept in the order's history)") }}</span>
					<Textarea v-model="form.reason" rows="2" autoResize maxlength="500" class="mt-1 w-full" :class="fieldFocusClass" />
				</label>
				<label class="block text-sm">
					<span class="text-gray-600">{{ ctrans("Payment method") }}</span>
					<Select v-model="form.method" :options="payments.methods" optionLabel="name" optionValue="code" class="mt-1 w-full" :class="fieldFocusClass" />
				</label>
				<label class="block text-sm">
					<span class="text-gray-600">{{ ctrans("Payment reference") }}</span>
					<InputText v-model="form.reference" maxlength="255" class="mt-1 w-full" :class="fieldFocusClass" />
				</label>
				<div class="block text-sm">
					<span class="text-gray-600">{{ needsStatement ? ctrans("Bank statement") : ctrans("Receipt (optional)") }}</span>
					<div class="mt-1 flex flex-wrap items-center gap-2">
						<FileUpload mode="basic" :auto="false" customUpload accept=".pdf,image/*" :maxFileSize="20000000" :chooseLabel="ctrans('Choose file')" :pt="chooseFilePt" @select="(event: { files: File[] }) => (form.file = event.files[0] ?? null)" />
					</div>
				</div>
			</div>
			<template #footer>
				<Button type="tertiary" :label="ctrans('Cancel')" @click="isPayDialogOpen = false">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
				<Button type="tertiary" :label="ctrans('Confirm payment')" :disabled="!canSubmit" :loading="isLoading" @click="confirmPayment">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</template>
		</Dialog>
	</div>
</template>
