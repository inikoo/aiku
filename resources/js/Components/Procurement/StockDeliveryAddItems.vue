<!--
    * Author: Vika Aqordi
    * Created on: 2026-10-15 14:35
    * Github: https://github.com/aqordeon
    * Copyright: 2026
-->

<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { router } from "@inertiajs/vue3"
import Checkbox from "primevue/checkbox"
import Dialog from "primevue/dialog"
import InputNumber from "primevue/inputnumber"
import InputText from "primevue/inputtext"
import { notify } from "@kyvg/vue3-notification"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faCheck, faTimes, faInfoCircle, faExclamationTriangle, faBox, faCube } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { routeType } from "@/types/route"

type AddableItem = {
	id: number
	purchase_order_id: number
	purchase_order_reference: string
	supplier: string | null
	agent_order_reference: string | null
	code: string | null
	name: string | null
	quantity_ordered: number
	quantity_awaiting_delivery: number
	units_per_sko: number
	units_per_carton: number
}

type QuantityMode = "sko" | "units"

const props = defineProps<{
	data: { route: routeType; items: AddableItem[] } | null | undefined
}>()

const visible = defineModel<boolean>("visible", { default: false })

const locale = useLocaleStore()

const search = ref("")
const mode = ref<QuantityMode>("sko")
const quantities = ref<Record<number, number | null>>({})
const isSubmitting = ref(false)

const modeOptions = computed(() => [
	{ label: ctrans("SKO"), value: "sko" as QuantityMode, icon: faBox },
	{ label: ctrans("Units"), value: "units" as QuantityMode, icon: faCube },
])

watch(visible, (isVisible) => {
	if (isVisible) {
		search.value = ""
		quantities.value = {}
	}
})

const items = computed(() => props.data?.items ?? [])

const filteredItems = computed(() => {
	const term = search.value.trim().toLowerCase()

	return term
		? items.value.filter(item => [item.code, item.name, item.purchase_order_reference, item.supplier, item.agent_order_reference]
			.some(value => value?.toLowerCase().includes(term)))
		: items.value
})

const purchaseOrders = computed(() => {
	const groups = new Map<number, { id: number; reference: string; supplier: string | null; agent_order_reference: string | null; items: AddableItem[] }>()

	for (const item of filteredItems.value) {
		if (!groups.has(item.purchase_order_id)) {
			groups.set(item.purchase_order_id, {
				id: item.purchase_order_id,
				reference: item.purchase_order_reference,
				supplier: item.supplier,
				agent_order_reference: item.agent_order_reference,
				items: [],
			})
		}
		groups.get(item.purchase_order_id)!.items.push(item)
	}

	return [...groups.values()]
})

const itemsById = computed(() => new Map(items.value.map(item => [item.id, item])))

const unitsPerSko = (item: AddableItem) => Number(item.units_per_sko) || 1

const toUnits = (item: AddableItem, quantity: number) => mode.value === "sko" ? quantity * unitsPerSko(item) : quantity

const fromUnits = (item: AddableItem, units: number) => mode.value === "sko" ? units / unitsPerSko(item) : units

const round = (value: number) => Math.round(value * 1000) / 1000

const formatNumber = (value: number) => locale.number(round(value))

const isSelected = (item: AddableItem) => item.id in quantities.value

const toggleItem = (item: AddableItem) => {
	const next = { ...quantities.value }

	if (isSelected(item)) {
		delete next[item.id]
	} else {
		next[item.id] = round(fromUnits(item, item.quantity_awaiting_delivery))
	}

	quantities.value = next
}

const isPurchaseOrderSelected = (purchaseOrder: { items: AddableItem[] }) => purchaseOrder.items.every(isSelected)

const togglePurchaseOrder = (purchaseOrder: { items: AddableItem[] }) => {
	const select = !isPurchaseOrderSelected(purchaseOrder)
	const next = { ...quantities.value }

	for (const item of purchaseOrder.items) {
		if (select && !(item.id in next)) {
			next[item.id] = round(fromUnits(item, item.quantity_awaiting_delivery))
		} else if (!select) {
			delete next[item.id]
		}
	}

	quantities.value = next
}

watch(mode, (newMode, oldMode) => {
	const next: Record<number, number | null> = {}

	for (const [id, quantity] of Object.entries(quantities.value)) {
		const item = itemsById.value.get(Number(id))
		if (!item || quantity === null) {
			next[Number(id)] = quantity
			continue
		}
		const units = oldMode === "sko" ? quantity * unitsPerSko(item) : quantity
		next[Number(id)] = round(newMode === "sko" ? units / unitsPerSko(item) : units)
	}

	quantities.value = next
})

const fillAll = (item: AddableItem) => {
	quantities.value = { ...quantities.value, [item.id]: round(fromUnits(item, item.quantity_awaiting_delivery)) }
}

const unitsOf = (item: AddableItem) => {
	const quantity = quantities.value[item.id]

	return quantity ? round(toUnits(item, quantity)) : 0
}

const breakdown = (item: AddableItem, units: number) => {
	const parts = [
		ctrans(":count units", { count: formatNumber(units) }),
		ctrans(":count SKO", { count: formatNumber(units / unitsPerSko(item)) }),
	]

	if (Number(item.units_per_carton) > 0) {
		parts.push(ctrans(":count cartons", { count: formatNumber(units / Number(item.units_per_carton)) }))
	}

	return parts.join(" · ")
}

const packing = (item: AddableItem) => {
	const sko = ctrans("1 SKO = :units units", { units: formatNumber(unitsPerSko(item)) })

	if (!(Number(item.units_per_carton) > 0)) {
		return sko
	}

	return `${sko} · ${ctrans("1 carton = :skos SKO (:units units)", {
		skos: formatNumber(Number(item.units_per_carton) / unitsPerSko(item)),
		units: formatNumber(Number(item.units_per_carton)),
	})}`
}

const problem = (item: AddableItem): { tone: "red" | "amber"; text: string } | null => {
	const quantity = quantities.value[item.id]
	if (!quantity || quantity <= 0) {
		return { tone: "red", text: ctrans("Enter a quantity") }
	}

	const units = unitsOf(item)
	if (units > round(item.quantity_awaiting_delivery)) {
		return { tone: "red", text: ctrans("Only :awaiting are awaiting delivery", { awaiting: breakdown(item, item.quantity_awaiting_delivery) }) }
	}

	if (Math.abs(units - Math.round(units)) > 0.001) {
		return { tone: "amber", text: ctrans("This is not a whole number of units, check the SKO quantity") }
	}

	const skos = units / unitsPerSko(item)
	if (Math.abs(skos - Math.round(skos)) > 0.001) {
		const wholeSkos = Math.floor(skos)
		return {
			tone: "amber",
			text: ctrans("Not a whole number of SKOs: :skos SKO and :units loose units", {
				skos: formatNumber(wholeSkos),
				units: formatNumber(units - wholeSkos * unitsPerSko(item)),
			}),
		}
	}

	return null
}

const selectedItems = computed(() => Object.keys(quantities.value).map(id => itemsById.value.get(Number(id))).filter((item): item is AddableItem => !!item))

const hasBlockingProblem = computed(() => selectedItems.value.some(item => problem(item)?.tone === "red"))

const totalUnits = computed(() => selectedItems.value.reduce((sum, item) => sum + unitsOf(item), 0))

const totalSkos = computed(() => selectedItems.value.reduce((sum, item) => sum + unitsOf(item) / unitsPerSko(item), 0))

const submit = () => {
	if (!props.data || selectedItems.value.length === 0 || hasBlockingProblem.value) {
		return
	}

	const addedCount = selectedItems.value.length

	router.post(route(props.data.route.name, props.data.route.parameters), {
		items: selectedItems.value.map(item => ({
			purchase_order_transaction_id: item.id,
			unit_quantity: unitsOf(item),
		})),
	}, {
		preserveScroll: true,
		onStart: () => { isSubmitting.value = true },
		onSuccess: () => {
			visible.value = false
			notify({
				title: ctrans("Items added"),
				text: addedCount === 1 ? ctrans("1 item added to this container") : ctrans(":count items added to this container", { count: addedCount }),
				type: "success",
			})
		},
		onFinish: () => { isSubmitting.value = false },
		onError: (errors: Record<string, string>) => {
			notify({
				title: ctrans("Failed to add items"),
				text: Object.values(errors)[0] ?? ctrans("Something went wrong"),
				type: "error",
			})
		},
	})
}
</script>

<template>
	<Dialog
		v-model:visible="visible"
		modal
		:header="ctrans('Add items to this container')"
		:style="{ width: '60rem', maxWidth: 'calc(100vw - 2rem)' }"
		:draggable="false"
	>
		<div class="mb-3 flex gap-2 rounded-lg border border-[--app-accent-muted] bg-[--app-accent-soft] p-3 text-sm text-gray-700">
			<FontAwesomeIcon :icon="faInfoCircle" class="mt-0.5 text-[--app-accent]" fixed-width aria-hidden="true" />
			<div class="space-y-1">
				<p>
					<span class="font-semibold">{{ ctrans("Unit") }}</span>: {{ ctrans("one single piece, the smallest thing we count.") }}
					<span class="font-semibold">{{ ctrans("SKO") }}</span>: {{ ctrans("the pack we store and sell, holding several units. Each line says how many.") }}
				</p>
				<p>{{ ctrans("Choose whether you type the quantity in SKOs or in units. Every line shows the result in units, SKOs and cartons before you add it, and what is not added stays on the purchase order for another container.") }}</p>
			</div>
		</div>

		<div class="mb-3 flex flex-wrap items-center gap-3">
			<InputText v-model="search" class="min-w-[14rem] flex-1" :placeholder="ctrans('Search code, name, purchase order or supplier')" />
			<div class="flex items-center gap-2 text-sm text-gray-600">
				{{ ctrans("Type quantities in") }}
				<SegmentedToggle v-model="mode" :options="modeOptions" :ariaLabel="ctrans('Type quantities in')" />
			</div>
		</div>

		<div v-if="!data" class="space-y-2">
			<div v-for="index in 4" :key="index" class="h-14 animate-pulse rounded-lg bg-gray-100" />
		</div>

		<div v-else class="max-h-[55vh] overflow-y-auto rounded-lg border border-gray-200">
			<div v-for="purchaseOrder in purchaseOrders" :key="purchaseOrder.id" class="border-b border-gray-200 last:border-b-0">
				<label class="sticky top-0 z-10 flex cursor-pointer items-center gap-3 bg-gray-50 px-3 py-2 text-sm">
					<Checkbox :modelValue="isPurchaseOrderSelected(purchaseOrder)" binary @update:modelValue="togglePurchaseOrder(purchaseOrder)" />
					<span class="font-semibold text-gray-800">{{ purchaseOrder.reference }}</span>
					<span class="truncate text-gray-500">{{ [purchaseOrder.supplier, purchaseOrder.agent_order_reference].filter(Boolean).join(" · ") }}</span>
					<span class="ml-auto whitespace-nowrap text-xs text-gray-500">{{ ctrans("Select whole purchase order") }}</span>
				</label>

				<div
					v-for="item in purchaseOrder.items"
					:key="item.id"
					class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-2 px-3 py-2.5 sm:grid-cols-[auto_1fr_auto]"
					:class="isSelected(item) ? 'bg-[--app-accent-soft]' : ''"
				>
					<Checkbox :modelValue="isSelected(item)" binary class="mt-0.5" @update:modelValue="toggleItem(item)" />
					<div class="min-w-0 text-sm">
						<div class="font-medium text-gray-800">
							{{ item.code ?? "-" }} <span class="font-normal text-gray-600">{{ item.name }}</span>
						</div>
						<div class="text-xs text-gray-500">{{ packing(item) }}</div>
						<div class="text-xs text-gray-700">
							{{ ctrans("Awaiting delivery") }}: <span class="font-medium">{{ breakdown(item, item.quantity_awaiting_delivery) }}</span>
							<span v-if="round(item.quantity_awaiting_delivery) < round(item.quantity_ordered)" class="text-gray-500">
								({{ ctrans("of :ordered units ordered, the rest is already in a container", { ordered: formatNumber(item.quantity_ordered) }) }})
							</span>
						</div>
					</div>

					<div v-if="isSelected(item)" class="col-span-2 flex flex-col items-end gap-1 sm:col-span-1">
						<div class="flex items-center gap-2">
							<InputNumber
								v-model="quantities[item.id]"
								:min="0"
								:maxFractionDigits="3"
								:suffix="mode === 'sko' ? ` ${ctrans('SKO')}` : ` ${ctrans('units')}`"
								inputClass="!w-32 !py-1 text-right"
								:aria-label="ctrans('Quantity')"
							/>
							<Button type="tertiary" size="xs" :label="ctrans('All')" @click="fillAll(item)" />
						</div>
						<div class="text-xs text-gray-600">= {{ breakdown(item, unitsOf(item)) }}</div>
						<div
							v-if="problem(item)"
							class="flex items-center gap-1 text-xs"
							:class="problem(item)?.tone === 'red' ? 'text-red-600' : 'text-amber-600'"
						>
							<FontAwesomeIcon :icon="faExclamationTriangle" fixed-width aria-hidden="true" />
							{{ problem(item)?.text }}
						</div>
					</div>
				</div>
			</div>

			<p v-if="purchaseOrders.length === 0" class="p-4 text-center text-sm text-gray-500">
				{{ items.length === 0 ? ctrans("No items of this agent are waiting for a container") : ctrans("No items match your search") }}
			</p>
		</div>

		<template #footer>
			<div class="flex w-full flex-wrap items-center justify-end gap-3">
				<span v-if="selectedItems.length" class="mr-auto text-sm text-gray-600">
					{{ ctrans(":items items · :units units · :skos SKO", {
						items: selectedItems.length,
						units: formatNumber(totalUnits),
						skos: formatNumber(totalSkos),
					}) }}
				</span>
				<Button type="tertiary" :label="ctrans('Cancel')" @click="visible = false">
					<template #icon>
						<FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
				<Button
					type="tertiary"
					:label="selectedItems.length === 1 ? ctrans('Add 1 item') : ctrans('Add :count items', { count: selectedItems.length })"
					:loading="isSubmitting"
					:disabled="selectedItems.length === 0 || hasBlockingProblem"
					@click="submit"
				>
					<template #icon>
						<FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>
		</template>
	</Dialog>
</template>
