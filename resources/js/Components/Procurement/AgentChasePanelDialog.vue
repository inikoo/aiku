<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from "vue"
import { router } from "@inertiajs/vue3"
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import Dialog from "primevue/dialog"
import Select from "primevue/select"
import InputText from "primevue/inputtext"
import DatePicker from "primevue/datepicker"
import Textarea from "primevue/textarea"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCheck, faBullhorn, faSpinnerThird } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import PurchaseOrderStages from "@/Components/Procurement/PurchaseOrderStages.vue"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { routeType } from "@/types/route"

library.add(faCheck, faBullhorn, faSpinnerThird)

const props = defineProps<{
	panelRoute: routeType | null
}>()

const emit = defineEmits<{ (e: "close"): void }>()

interface Panel {
	reference: string
	supplier: string
	state: string
	eta: string | null
	can_edit: boolean
	confirm_route: routeType | null
	stages: InstanceType<typeof PurchaseOrderStages>["$props"]["stages"]
	stage_route: routeType
	chase_route: routeType | null
	channels: { value: string; label: string }[]
	chases: { id: number; chased_at: string; channel: string; contact: string | null; promised_at: string | null; note: string | null; by: string | null }[]
}

const visible = computed({
	get: () => !!props.panelRoute,
	set: (value: boolean) => {
		if (!value) {
			emit("close")
		}
	},
})

const panel = ref<Panel | null>(null)
const loading = ref(false)

async function load() {
	if (!props.panelRoute) {
		return
	}
	loading.value = true
	try {
		panel.value = (await axios.get(route(props.panelRoute.name, props.panelRoute.parameters))).data
	} catch (error: any) {
		notify({ title: ctrans("Something went wrong"), text: error?.response?.data?.message ?? "", type: "error" })
	} finally {
		loading.value = false
	}
}

const stopListening = router.on("success", () => {
	if (props.panelRoute) {
		load()
	}
})
onBeforeUnmount(() => stopListening())

const refreshDashboard = () => router.reload({ only: ["dashboard"] })

const confirming = ref(false)
async function confirmOrder() {
	if (!panel.value?.confirm_route) {
		return
	}
	confirming.value = true
	try {
		await axios.patch(route(panel.value.confirm_route.name, panel.value.confirm_route.parameters), {})
		refreshDashboard()
	} catch (error: any) {
		notify({ title: ctrans("Something went wrong"), text: error?.response?.data?.message ?? "", type: "error" })
	} finally {
		confirming.value = false
	}
}

const toIso = (date: Date | null) => date ? `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}` : null

const channel = ref<string | null>(null)
const contact = ref("")
const promisedAt = ref<Date | null>(null)
const note = ref("")
const errors = ref<Record<string, string>>({})
const saving = ref(false)

function resetForm() {
	channel.value = null
	contact.value = ""
	promisedAt.value = null
	note.value = ""
	errors.value = {}
}

watch(() => props.panelRoute, (value) => {
	panel.value = null
	resetForm()
	if (value) {
		load()
	}
}, { immediate: true })

async function logChase() {
	if (!panel.value?.chase_route) {
		return
	}
	saving.value = true
	errors.value = {}
	try {
		await axios.post(route(panel.value.chase_route.name, panel.value.chase_route.parameters), {
			channel: channel.value,
			contact: contact.value.trim() || null,
			promised_at: toIso(promisedAt.value),
			note: note.value.trim() || null,
		})
		resetForm()
		refreshDashboard()
	} catch (error: any) {
		errors.value = Object.fromEntries(Object.entries(error?.response?.data?.errors ?? {}).map(([key, messages]) => [key, (messages as string[])[0]]))
		if (!Object.keys(errors.value).length) {
			notify({ title: ctrans("Something went wrong"), text: error?.response?.data?.message ?? "", type: "error" })
		}
	} finally {
		saving.value = false
	}
}

const today = new Date()
</script>

<template>
	<Dialog v-model:visible="visible" modal :header="panel ? `${panel.reference} · ${panel.supplier}` : ctrans('Purchase order')" :style="{ width: '72rem' }" :breakpoints="{ '1280px': '95vw' }" :draggable="false">
		<div v-if="loading && !panel" class="py-10 text-center text-gray-400">
			<FontAwesomeIcon icon="fal fa-spinner-third" spin fixed-width aria-hidden="true" />
		</div>
		<div v-else-if="panel" class="space-y-5">
			<div class="flex flex-wrap items-center justify-between gap-3 text-sm text-gray-600">
				<span>
					{{ ctrans("Expected") }}: <span class="font-medium text-gray-900">{{ panel.eta ? useFormatTime(panel.eta) : ctrans("not set") }}</span>
				</span>
				<Button v-if="panel.confirm_route" type="tertiary" :label="ctrans('Supplier confirmed the order')" :loading="confirming" @click="confirmOrder">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>

			<div class="overflow-hidden rounded-md border border-gray-200">
				<PurchaseOrderStages :stages="panel.stages" :route="panel.stage_route" :uploadRoute="null" />
			</div>

			<div class="grid gap-5 lg:grid-cols-2">
				<section v-if="panel.chase_route">
					<h3 class="mb-2 text-sm font-semibold text-gray-700">{{ ctrans("Log a chase") }}</h3>
					<div class="grid grid-cols-2 gap-3 text-sm">
						<label class="flex flex-col gap-1">
							<span class="text-xs text-gray-500">{{ ctrans("Channel") }}</span>
							<Select v-model="channel" :options="panel.channels" optionLabel="label" optionValue="value" :placeholder="ctrans('How')" size="small" :invalid="!!errors.channel" />
							<span v-if="errors.channel" class="text-xs text-red-600">{{ errors.channel }}</span>
						</label>
						<label class="flex flex-col gap-1">
							<span class="text-xs text-gray-500">{{ ctrans("Contact") }}</span>
							<InputText v-model="contact" size="small" :placeholder="ctrans('Who you spoke to')" />
						</label>
						<label class="flex flex-col gap-1">
							<span class="text-xs text-gray-500">{{ ctrans("Promised date") }}</span>
							<DatePicker v-model="promisedAt" :minDate="today" dateFormat="dd M yy" showIcon size="small" :invalid="!!errors.promised_at" />
							<span v-if="errors.promised_at" class="text-xs text-red-600">{{ errors.promised_at }}</span>
						</label>
						<label class="col-span-2 flex flex-col gap-1">
							<span class="text-xs text-gray-500">{{ ctrans("Note") }}</span>
							<Textarea v-model="note" rows="2" autoResize />
						</label>
					</div>
					<p class="mt-2 text-xs text-gray-500">{{ ctrans("A promised date later than the expected date becomes the new expected date.") }}</p>
					<div class="mt-3">
						<Button type="tertiary" :label="ctrans('Save chase')" :loading="saving" :disabled="!channel" @click="logChase">
							<template #icon>
								<FontAwesomeIcon icon="fal fa-bullhorn" class="text-green-600" fixed-width aria-hidden="true" />
							</template>
						</Button>
					</div>
				</section>

				<section>
					<h3 class="mb-2 text-sm font-semibold text-gray-700">{{ ctrans("Chases") }}</h3>
					<p v-if="!panel.chases.length" class="text-sm italic text-gray-400">{{ ctrans("Not chased yet") }}</p>
					<ul v-else class="divide-y divide-gray-100 text-sm">
						<li v-for="chase in panel.chases" :key="chase.id" class="py-2">
							<div class="flex flex-wrap items-center justify-between gap-2">
								<span class="font-medium text-gray-700">{{ useFormatTime(chase.chased_at) }} · {{ chase.channel }}<span v-if="chase.contact" class="font-normal text-gray-500"> · {{ chase.contact }}</span></span>
								<span class="text-xs text-gray-500">{{ chase.by }}</span>
							</div>
							<div v-if="chase.promised_at" class="text-xs text-gray-600">{{ ctrans("Promised :date", { date: useFormatTime(chase.promised_at) }) }}</div>
							<div v-if="chase.note" class="text-xs text-gray-600">{{ chase.note }}</div>
						</li>
					</ul>
				</section>
			</div>
		</div>
	</Dialog>
</template>
