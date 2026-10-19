<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 18 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { Head, Link, router } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"
import ConfirmDialog from "primevue/confirmdialog"
import DatePicker from "primevue/datepicker"
import Dialog from "primevue/dialog"
import FileUpload from "primevue/fileupload"
import InputNumber from "primevue/inputnumber"
import InputText from "primevue/inputtext"
import Select from "primevue/select"
import Textarea from "primevue/textarea"
import { useConfirm } from "primevue/useconfirm"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faBook, faCheck, faExclamationTriangle, faPaperclip, faPencil, faPlus, faTimes, faTrashAlt } from "@fal"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Table from "@/Components/Table/Table.vue"
import { capitalize } from "@/Composables/capitalize"
import { useFormatTime } from "@/Composables/useFormatTime"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"

library.add(faBook, faCheck, faExclamationTriangle, faPaperclip, faPencil, faPlus, faTimes, faTrashAlt)

interface Wallet {
	organisation_id: number
	code: string
	name: string
	currency_code: string
	received: number
	paid: number
	balance: number
}

interface Option {
	id: number
	organisation_id: number
	label: string
}

interface EntryRow {
	id: number
	date: string
	client_code: string
	type_label: string
	money_in: number | null
	money_out: number | null
	currency_code: string
	original_amount: number | null
	original_currency_code: string | null
	supplier_code: string | null
	purchase_order: { reference: string; route: routeType } | null
	stock_delivery: { reference: string; route: routeType } | null
	reference: string | null
	notes: string | null
	receipt: string | null
	type: string
	organisation_id: number
	purchase_order_id: number | null
	stock_delivery_id: number | null
	needs_review: boolean
	review_reason: string | null
	delete_route: routeType | null
	update_route: routeType | null
}

const props = defineProps<{
	title: string
	pageHead: PageHeadingTypes
	wallets: Wallet[]
	form: {
		store_route: routeType
		currency_code: string
		clients: { id: number; label: string; currency_code: string }[]
		types: { value: string; label: string; money_in: boolean; needs_order: boolean }[]
		purchase_orders: Option[]
		containers: Option[]
	} | null
	data: object
}>()

const locale = useLocaleStore()
const money = (code: string, amount: number) => locale.currencyFormat(code, amount)

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent] focus:!border-[--app-accent]"
const fullWidthInput = { pcInputText: { root: { class: "!w-full" } } }
const chooseFilePt = { pcChooseButton: { root: { class: "!border !border-gray-300 !bg-white !text-gray-700 hover:!bg-gray-50" } } }

const onError = (errors: Record<string, string>) =>
	notify({ title: ctrans("Something went wrong"), text: Object.values(errors ?? {})[0], type: "error" })

const isOpen = ref(false)
const saving = ref(false)
const editing = ref<EntryRow | null>(null)

const emptyEntry = () => ({
	organisation_id: null as number | null,
	type: "supplier_deposit",
	date: new Date() as Date | null,
	amount: null as number | null,
	original_amount: null as number | null,
	purchase_order_id: null as number | null,
	stock_delivery_id: null as number | null,
	reference: "",
	notes: "",
	receipt: null as File | null,
})
const entry = ref(emptyEntry())

watch(
	() => entry.value.organisation_id,
	(organisationId, previous) => {
		if (previous === undefined || editing.value) return
		entry.value.purchase_order_id = null
		entry.value.stock_delivery_id = null
	}
)

const selectedType = computed(() => props.form?.types.find((type) => type.value === entry.value.type))
const selectedClient = computed(() => props.form?.clients.find((client) => client.id === entry.value.organisation_id))
const clientOrders = computed(() => props.form?.purchase_orders.filter((order) => order.organisation_id === entry.value.organisation_id) ?? [])
const clientContainers = computed(() => props.form?.containers.filter((container) => container.organisation_id === entry.value.organisation_id) ?? [])

const typeOptions = computed(() =>
	editing.value ? (props.form?.types ?? []).filter((type) => type.money_in === (editing.value!.money_in !== null)) : props.form?.types ?? []
)

const canSave = computed(
	() =>
		!!entry.value.organisation_id &&
		!!entry.value.date &&
		!!entry.value.amount &&
		(!selectedType.value?.needs_order || !!entry.value.purchase_order_id)
)

const toIsoDate = (date: Date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`

const openEntry = () => {
	editing.value = null
	entry.value = emptyEntry()
	isOpen.value = true
}

const openEdit = (row: EntryRow) => {
	editing.value = row
	entry.value = {
		...emptyEntry(),
		organisation_id: row.organisation_id,
		type: row.type,
		date: new Date(`${row.date}T00:00:00`),
		amount: row.money_in ?? row.money_out,
		purchase_order_id: row.purchase_order_id,
		stock_delivery_id: row.stock_delivery_id,
		reference: row.reference ?? "",
		notes: row.notes ?? "",
	}
	isOpen.value = true
}

const saveEdit = () => {
	const row = editing.value
	if (!row?.update_route || !canSave.value) return

	router.patch(
		route(row.update_route.name, row.update_route.parameters),
		{
			date: toIsoDate(entry.value.date as Date),
			type: entry.value.type,
			purchase_order_id: entry.value.purchase_order_id,
			stock_delivery_id: entry.value.stock_delivery_id,
			reference: entry.value.reference || null,
			notes: entry.value.notes || null,
		},
		{
			preserveScroll: true,
			onStart: () => (saving.value = true),
			onFinish: () => (saving.value = false),
			onSuccess: () => {
				isOpen.value = false
				notify({ title: ctrans("Saved"), type: "success" })
			},
			onError,
		}
	)
}

const save = () => {
	if (!props.form || !canSave.value) return

	router.post(
		route(props.form.store_route.name, props.form.store_route.parameters),
		{
			organisation_id: entry.value.organisation_id,
			type: entry.value.type,
			date: toIsoDate(entry.value.date as Date),
			amount: entry.value.amount,
			original_amount: selectedType.value?.value === "transfer_received" ? entry.value.original_amount : null,
			purchase_order_id: entry.value.purchase_order_id,
			stock_delivery_id: entry.value.stock_delivery_id,
			reference: entry.value.reference || null,
			notes: entry.value.notes || null,
			receipt: entry.value.receipt,
		},
		{
			preserveScroll: true,
			forceFormData: true,
			onStart: () => (saving.value = true),
			onFinish: () => (saving.value = false),
			onSuccess: () => {
				isOpen.value = false
				notify({ title: ctrans("Recorded"), type: "success" })
			},
			onError,
		}
	)
}

const confirm = useConfirm()

const confirmRemove = (row: EntryRow) =>
	confirm.require({
		group: "agent-ledger",
		header: ctrans("Remove this line"),
		message: ctrans("Remove the :type of :amount from :date?", {
			type: row.type_label,
			amount: money(row.currency_code, row.money_in ?? row.money_out ?? 0),
			date: useFormatTime(row.date),
		}),
		rejectLabel: ctrans("Cancel"),
		acceptLabel: ctrans("Remove"),
		accept: () =>
			router.delete(route(row.delete_route!.name, row.delete_route!.parameters), {
				preserveScroll: true,
				onSuccess: () => notify({ title: ctrans("Removed"), type: "success" }),
				onError,
			}),
	})
</script>

<template>
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead">
		<template #other>
			<Button v-if="form" type="tertiary" :label="ctrans('Record money')" @click="openEntry">
				<template #icon>
					<FontAwesomeIcon icon="fal fa-plus" class="text-gray-500" fixed-width aria-hidden="true" />
				</template>
			</Button>
		</template>
	</PageHeading>

	<div class="mx-4 mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
		<div v-for="wallet in wallets" :key="`${wallet.organisation_id}-${wallet.currency_code}`" class="rounded-lg border border-gray-200 bg-white p-4">
			<div class="flex items-baseline justify-between gap-2">
				<span class="font-semibold uppercase text-gray-700">{{ wallet.code }}</span>
				<span class="truncate text-xs text-gray-400">{{ wallet.name }}</span>
			</div>
			<div class="mt-2 text-xs text-gray-400">{{ ctrans("Balance") }}</div>
			<div class="text-lg font-semibold" :class="wallet.balance < 0 ? 'text-red-600' : 'text-gray-800'">
				{{ money(wallet.currency_code, wallet.balance) }}
			</div>
			<div class="mt-2 grid grid-cols-2 gap-2 text-xs text-gray-500">
				<div>
					<div class="text-gray-400">{{ ctrans("Received") }}</div>
					<div class="whitespace-nowrap">{{ money(wallet.currency_code, wallet.received) }}</div>
				</div>
				<div>
					<div class="text-gray-400">{{ ctrans("Paid out") }}</div>
					<div class="whitespace-nowrap">{{ money(wallet.currency_code, wallet.paid) }}</div>
				</div>
			</div>
		</div>
	</div>

	<Table :resource="data" class="mt-5">
		<template #cell(date)="{ item }">
			<span class="whitespace-nowrap">{{ useFormatTime(item.date) }}</span>
		</template>

		<template #cell(client_code)="{ item }">
			<span class="font-semibold uppercase text-gray-700">{{ item.client_code }}</span>
		</template>

		<template #cell(type)="{ item }">
			{{ item.type_label }}
		</template>

		<template #cell(for)="{ item }">
			<div class="flex flex-wrap items-center gap-x-2 text-sm">
				<span v-if="item.supplier_code" class="whitespace-nowrap text-gray-700">{{ item.supplier_code }}</span>
				<Link v-if="item.purchase_order" :href="route(item.purchase_order.route.name, item.purchase_order.route.parameters)" class="secondaryLink whitespace-nowrap">
					{{ item.purchase_order.reference }}
				</Link>
				<Link v-if="item.stock_delivery" :href="route(item.stock_delivery.route.name, item.stock_delivery.route.parameters)" class="secondaryLink whitespace-nowrap">
					{{ item.stock_delivery.reference }}
				</Link>
				<span v-if="item.notes" class="text-xs text-gray-400">{{ item.notes }}</span>
			</div>
			<div v-if="item.needs_review" class="mt-1 inline-flex items-center gap-1 rounded bg-amber-50 px-1.5 py-0.5 text-xs text-amber-700">
				<FontAwesomeIcon icon="fal fa-exclamation-triangle" fixed-width aria-hidden="true" />
				{{ ctrans("To review") }}: {{ item.review_reason }}
			</div>
		</template>

		<template #cell(money_in)="{ item }">
			<div v-if="item.money_in !== null" class="whitespace-nowrap text-right">
				{{ money(item.currency_code, item.money_in) }}
				<div v-if="item.original_amount" class="text-xs text-gray-400">
					{{ money(item.original_currency_code, item.original_amount) }}
				</div>
			</div>
		</template>

		<template #cell(money_out)="{ item }">
			<div v-if="item.money_out !== null" class="whitespace-nowrap text-right">{{ money(item.currency_code, item.money_out) }}</div>
		</template>

		<template #cell(reference)="{ item }">
			<span class="whitespace-nowrap">{{ item.reference }}</span>
			<a v-if="item.receipt" :href="route('grp.media.download', { id: item.receipt })" target="_blank" class="ml-2 text-gray-500 hover:text-gray-800" :title="ctrans('Receipt')">
				<FontAwesomeIcon icon="fal fa-paperclip" fixed-width aria-hidden="true" />
			</a>
		</template>

		<template #cell(actions)="{ item }">
			<button v-if="item.update_route" type="button" class="mr-2 text-gray-400 hover:text-gray-700" :title="ctrans('Edit')" :aria-label="ctrans('Edit')" @click="openEdit(item)">
				<FontAwesomeIcon icon="fal fa-pencil" fixed-width aria-hidden="true" />
			</button>
			<button v-if="item.delete_route" type="button" class="text-gray-400 hover:text-red-600" :title="ctrans('Remove')" :aria-label="ctrans('Remove')" @click="confirmRemove(item)">
				<FontAwesomeIcon icon="fal fa-trash-alt" fixed-width aria-hidden="true" />
			</button>
		</template>
	</Table>

	<Dialog v-if="form" v-model:visible="isOpen" modal :header="editing ? ctrans('Edit line') : ctrans('Record money')" :style="{ width: '32rem', maxWidth: 'calc(100vw - 2rem)' }">
		<div class="space-y-3">
			<label class="block text-sm">
				<span class="text-gray-600">{{ ctrans("Client") }}</span>
				<Select v-model="entry.organisation_id" :options="form.clients" optionLabel="label" optionValue="id" :disabled="!!editing" class="mt-1 w-full" :class="fieldFocusClass" />
			</label>
			<label class="block text-sm">
				<span class="text-gray-600">{{ ctrans("Type") }}</span>
				<Select v-model="entry.type" :options="typeOptions" optionLabel="label" optionValue="value" class="mt-1 w-full" :class="fieldFocusClass">
					<template #option="{ option }">
						<span>{{ option.label }}</span>
						<span class="ml-2 text-xs" :class="option.money_in ? 'text-green-600' : 'text-gray-400'">{{ option.money_in ? ctrans("In") : ctrans("Out") }}</span>
					</template>
				</Select>
			</label>
			<div class="grid grid-cols-2 gap-3">
				<label class="block text-sm">
					<span class="text-gray-600">{{ ctrans("Date") }}</span>
					<DatePicker v-model="entry.date" dateFormat="dd/mm/yy" :maxDate="new Date()" :manualInput="false" showIcon iconDisplay="input" class="mt-1 w-full" :class="fieldFocusClass" :pt="fullWidthInput" />
				</label>
				<label class="block text-sm">
					<span class="text-gray-600">{{ ctrans("Amount") }}</span>
					<InputNumber v-model="entry.amount" mode="currency" :currency="form.currency_code" :min="0" :disabled="!!editing" class="mt-1 w-full" :class="fieldFocusClass" :pt="fullWidthInput" />
				</label>
			</div>
			<label v-if="!editing && selectedType?.value === 'transfer_received' && selectedClient" class="block text-sm">
				<span class="text-gray-600">{{ ctrans("Amount the client sent (:currency)", { currency: selectedClient.currency_code }) }}</span>
				<InputNumber v-model="entry.original_amount" mode="currency" :currency="selectedClient.currency_code" :min="0" class="mt-1 w-full" :class="fieldFocusClass" :pt="fullWidthInput" />
			</label>
			<label class="block text-sm">
				<span class="text-gray-600">{{ selectedType?.needs_order ? ctrans("Supplier order") : ctrans("Supplier order (optional)") }}</span>
				<Select v-model="entry.purchase_order_id" :options="clientOrders" optionLabel="label" optionValue="id" filter showClear :disabled="!entry.organisation_id" class="mt-1 w-full" :class="fieldFocusClass" />
			</label>
			<label class="block text-sm">
				<span class="text-gray-600">{{ ctrans("Container (optional)") }}</span>
				<Select v-model="entry.stock_delivery_id" :options="clientContainers" optionLabel="label" optionValue="id" filter showClear :disabled="!entry.organisation_id" class="mt-1 w-full" :class="fieldFocusClass" />
			</label>
			<label class="block text-sm">
				<span class="text-gray-600">{{ ctrans("Reference") }}</span>
				<InputText v-model="entry.reference" maxlength="255" class="mt-1 w-full" :class="fieldFocusClass" />
			</label>
			<label class="block text-sm">
				<span class="text-gray-600">{{ ctrans("Notes") }}</span>
				<Textarea v-model="entry.notes" rows="2" autoResize class="mt-1 w-full" :class="fieldFocusClass" />
			</label>
			<div v-if="!editing" class="block text-sm">
				<span class="text-gray-600">{{ ctrans("Receipt (optional)") }}</span>
				<div class="mt-1">
					<FileUpload mode="basic" :auto="false" customUpload accept=".pdf,image/*" :maxFileSize="20000000" :chooseLabel="ctrans('Choose file')" :pt="chooseFilePt" @select="(event: { files: File[] }) => (entry.receipt = event.files[0] ?? null)" />
				</div>
			</div>
		</div>
		<div class="mt-5 flex justify-end gap-2">
			<Button type="tertiary" :label="ctrans('Cancel')" @click="isOpen = false">
				<template #icon>
					<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
				</template>
			</Button>
			<Button type="tertiary" :label="editing ? ctrans('Save') : ctrans('Record')" :disabled="!canSave" :loading="saving" @click="editing ? saveEdit() : save()">
				<template #icon>
					<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
				</template>
			</Button>
		</div>
	</Dialog>

	<ConfirmDialog group="agent-ledger" :style="{ width: '28rem', maxWidth: 'calc(100vw - 2rem)' }">
		<template #container="{ message, acceptCallback, rejectCallback }">
			<div class="rounded-lg bg-white p-5">
				<div class="flex items-center gap-2 font-semibold text-gray-900">
					<FontAwesomeIcon icon="fal fa-exclamation-triangle" class="text-amber-500" fixed-width aria-hidden="true" />
					{{ message.header }}
				</div>
				<p class="mt-3 text-sm text-gray-600">{{ message.message }}</p>
				<div class="mt-5 flex justify-end gap-2">
					<Button type="tertiary" :label="message.rejectLabel" @click="rejectCallback">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
					<Button type="tertiary" :label="message.acceptLabel" @click="acceptCallback">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-trash-alt" class="text-red-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
				</div>
			</div>
		</template>
	</ConfirmDialog>
</template>
