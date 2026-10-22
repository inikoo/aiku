<!--
    * Author: Vika Aqordi
    * Created on: 2026-10-21 15:29
    * Github: https://github.com/aqordeon
    * Copyright: 2026
-->

<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { router } from "@inertiajs/vue3"
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import Dialog from "primevue/dialog"
import Select from "primevue/select"
import InputNumber from "primevue/inputnumber"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Image from "@common/Components/Image.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faCheck, faTimes, faExclamationTriangle, faPlus } from "@fal"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { routeType } from "@/types/route"

type Container = {
	id: number
	reference: string
	date: string | null
	number_purchase_orders: number
}

const props = defineProps<{
	item: {
		id: number
		code: string
		name?: string | null
		image_thumbnail?: object | null
		agent_warehouse: {
			quantity_awaiting_delivery: number
			quantity_packed_not_on_container: number
			containers_route: routeType | null
			new_container_route: routeType | null
			organisation_code: string | null
		}
	}
}>()

const visible = defineModel<boolean>("visible", { default: false })

const locale = useLocaleStore()

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"

const containers = ref<Container[]>([])
const isFetching = ref(false)
const selectedContainerId = ref<number | null>(null)
const isNewContainer = ref(false)
const quantity = ref<number | null>(null)
const isSaving = ref(false)

const awaiting = computed(() => props.item.agent_warehouse.quantity_awaiting_delivery)
const packedNotOnContainer = computed(() => props.item.agent_warehouse.quantity_packed_not_on_container)
const notPackedYet = computed(() => Math.max(0, Math.round(((quantity.value ?? 0) - packedNotOnContainer.value) * 1000) / 1000))

const organisationCode = computed(() => props.item.agent_warehouse.organisation_code ?? "")

const containerOptions = computed(() =>
	containers.value.map((container) => ({
		id: container.id,
		label: `${container.reference} (${ctrans(":count purchase orders", { count: container.number_purchase_orders })})`,
	}))
)

function chooseNewContainer() {
	isNewContainer.value = true
	selectedContainerId.value = null
}

watch(selectedContainerId, (containerId) => {
	if (containerId) {
		isNewContainer.value = false
	}
})

const hasFetched = ref(false)

let searchTimer: ReturnType<typeof setTimeout> | null = null

async function fetchContainers(search?: string) {
	const containersRoute = props.item.agent_warehouse.containers_route
	if (!containersRoute) {
		return
	}
	isFetching.value = true
	try {
		const response = await axios.get(route(containersRoute.name, containersRoute.parameters), { params: { search: search || undefined } })
		containers.value = response.data.data
		hasFetched.value = true
		if (!search && !containers.value.length && props.item.agent_warehouse.new_container_route && !selectedContainerId.value) {
			isNewContainer.value = true
		}
	} catch (error: any) {
		notify({ title: ctrans("Something went wrong"), text: error?.response?.data?.message ?? "", type: "error" })
	} finally {
		isFetching.value = false
	}
}

function onFilter(event: { value: string }) {
	if (searchTimer) {
		clearTimeout(searchTimer)
	}
	searchTimer = setTimeout(() => fetchContainers(event.value), 300)
}

watch(
	[visible, () => props.item.id],
	([isVisible]) => {
		if (!isVisible) {
			return
		}
		selectedContainerId.value = null
		isNewContainer.value = false
		containers.value = []
		hasFetched.value = false
		quantity.value = packedNotOnContainer.value > 0 ? Math.min(packedNotOnContainer.value, awaiting.value) : awaiting.value
		fetchContainers()
	},
	{ immediate: true }
)

const canSubmit = computed(() => (isNewContainer.value || !!selectedContainerId.value) && !!quantity.value && quantity.value > 0 && quantity.value <= awaiting.value)

function submit() {
	const newContainerRoute = props.item.agent_warehouse.new_container_route
	if (!canSubmit.value || (isNewContainer.value && !newContainerRoute)) {
		return
	}

	router.post(
		isNewContainer.value && newContainerRoute
			? route(newContainerRoute.name, newContainerRoute.parameters)
			: route("grp.models.stock-delivery.purchase_order_items.add", { stockDelivery: selectedContainerId.value }),
		isNewContainer.value
			? { unit_quantity: quantity.value }
			: { items: [{ purchase_order_transaction_id: props.item.id, unit_quantity: quantity.value }] },
		{
			preserveScroll: true,
			onStart: () => (isSaving.value = true),
			onFinish: () => (isSaving.value = false),
			onSuccess: () => {
				visible.value = false
				notify({ title: ctrans("Success"), text: ctrans(":units units of :code added to the container", { units: quantity.value ?? 0, code: props.item.code }), type: "success" })
			},
			onError: (errors: Record<string, string>) =>
				notify({ title: ctrans("Something went wrong"), text: Object.values(errors ?? {})[0], type: "error" }),
		}
	)
}
</script>

<template>
	<Dialog
		v-model:visible="visible"
		modal
		:header="ctrans('Add :code to a container', { code: item.code })"
		:style="{ width: '28rem', maxWidth: 'calc(100vw - 2rem)' }"
		:draggable="false"
	>
		<div class="space-y-4 text-sm">
			<div class="flex items-center gap-3">
				<Image :src="item.image_thumbnail" imageCover class="h-16 w-16 shrink-0 overflow-hidden rounded border border-gray-200" />
				<div class="min-w-0">
					<div class="whitespace-nowrap font-medium text-gray-800">{{ item.code }}</div>
					<div v-if="item.name" class="text-xs text-gray-500">{{ item.name }}</div>
				</div>
			</div>

			<div class="space-y-1">
				<span class="block text-xs text-gray-500">{{ ctrans("Container") }}</span>
				<div v-if="isNewContainer" class="flex items-center gap-2 rounded-md border border-[--app-accent-muted] bg-[--app-accent-soft] px-3 py-2">
					<FontAwesomeIcon :icon="faPlus" class="text-[--app-accent]" fixed-width aria-hidden="true" />
					<span class="text-gray-700">{{ ctrans("A new container for :organisation will be opened", { organisation: organisationCode }) }}</span>
				</div>
				<Select
					v-else
					v-model="selectedContainerId"
					:options="containerOptions"
					optionLabel="label"
					optionValue="id"
					filter
					:filterPlaceholder="ctrans('Search by reference')"
					:loading="isFetching"
					:placeholder="ctrans('Choose a container in process')"
					:emptyMessage="isFetching ? ctrans('Loading…') : ctrans('No container in process for :organisation yet', { organisation: organisationCode })"
					:emptyFilterMessage="isFetching ? ctrans('Loading…') : ctrans('No container found')"
					:class="fieldFocusClass"
					fluid
					@filter="onFilter"
				/>
				<span v-if="hasFetched && !containers.length" class="block text-xs text-gray-500">
					{{ ctrans("No container in process for :organisation yet, containers of other clients are not listed", { organisation: organisationCode }) }}
				</span>
				<button
					v-if="item.agent_warehouse.new_container_route && !isNewContainer"
					type="button"
					class="text-xs text-[--app-accent] underline-offset-2 hover:underline"
					@click="chooseNewContainer"
				>
					{{ ctrans("Or open a new container for :organisation", { organisation: organisationCode }) }}
				</button>
				<button
					v-else-if="isNewContainer && containers.length"
					type="button"
					class="text-xs text-[--app-accent] underline-offset-2 hover:underline"
					@click="isNewContainer = false"
				>
					{{ ctrans("Or choose a container in process") }}
				</button>
			</div>

			<label class="block space-y-1">
				<span class="block text-xs text-gray-500">{{ ctrans("Units") }}</span>
				<InputNumber v-model="quantity" :min="0" :max="awaiting" :maxFractionDigits="3" :class="fieldFocusClass" fluid />
				<span class="block text-xs text-gray-500">
					{{ ctrans(":units units not in a container yet, :packed of them packed", { units: locale.number(awaiting), packed: locale.number(Math.min(packedNotOnContainer, awaiting)) }) }}
				</span>
			</label>

			<p v-if="notPackedYet > 0" class="flex gap-2 rounded-md border border-amber-200 bg-amber-50 p-2 text-xs text-amber-800">
				<FontAwesomeIcon :icon="faExclamationTriangle" class="mt-0.5 text-amber-500" fixed-width aria-hidden="true" />
				<span>{{ ctrans(":units of these units are not packed yet", { units: locale.number(notPackedYet) }) }}</span>
			</p>
		</div>

		<template #footer>
			<Button type="tertiary" :label="ctrans('Cancel')" @click="visible = false">
				<template #icon>
					<FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
				</template>
			</Button>
			<Button type="secondary" full :label="ctrans('Add to container')" :loading="isSaving" :disabled="!canSubmit" @click="submit">
				<template #icon>
					<FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" />
				</template>
			</Button>
		</template>
	</Dialog>
</template>
