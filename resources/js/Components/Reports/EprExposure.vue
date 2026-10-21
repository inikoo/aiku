<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Oct 2026 12:00:00 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref } from "vue"
import { router } from "@inertiajs/vue3"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faGlobeEurope, faExclamationTriangle, faPencil, faCheck, faTimes, faTrashAlt, faHome } from "@fal"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import Dialog from "primevue/dialog"
import InputText from "primevue/inputtext"
import DatePicker from "primevue/datepicker"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"

library.add(faGlobeEurope, faExclamationTriangle, faPencil, faCheck, faTimes, faTrashAlt, faHome)

type Registration = { registration_number: string; scheme_operator: string | null; registered_on: string | null; recorded_by: string | null; recorded_at: string }
type CountryRow = {
	country_id: number
	code: string
	name: string
	own: boolean
	has_scheme: boolean
	consumer_kg: number
	trade_kg: number
	kg: number
	share: number
	months: Record<string, number>
	registration: Registration | null
	unregistered: boolean
}

const props = defineProps<{
	data?: {
		from: string
		to: string
		has_data: boolean
		months: string[]
		kg: number
		abroad_consumer_kg: number
		abroad_trade_kg: number
		unknown_country_kg: number
		unregistered: number
		countries: CountryRow[]
	}
	organisationId: number
}>()

const locale = useLocaleStore()
const kg = (value: number) => locale.number(Math.round(value))
const monthLabel = (month: string) => new Date(`${month}-01T00:00:00`).toLocaleDateString(undefined, { month: "short", year: "2-digit" })
const date = (iso: string) => new Date(`${iso}T00:00:00`).toLocaleDateString(undefined, { day: "numeric", month: "short", year: "numeric" })
const toIso = (value: Date | null) => (value ? `${value.getFullYear()}-${String(value.getMonth() + 1).padStart(2, "0")}-${String(value.getDate()).padStart(2, "0")}` : null)

const editing = ref<CountryRow | null>(null)
const form = ref({ registration_number: "", scheme_operator: "", registered_on: null as Date | null })
const saving = ref(false)
const errors = ref<Record<string, string>>({})

const edit = (row: CountryRow) => {
	editing.value = row
	errors.value = {}
	form.value = {
		registration_number: row.registration?.registration_number ?? "",
		scheme_operator: row.registration?.scheme_operator ?? "",
		registered_on: row.registration?.registered_on ? new Date(`${row.registration.registered_on}T00:00:00`) : null,
	}
}

const save = (remove = false) => {
	if (!editing.value) return
	saving.value = true
	router.post(
		route("grp.models.epr_registration.update", { organisation: props.organisationId }),
		remove
			? { country_code: editing.value.code, registration_number: null }
			: { country_code: editing.value.code, registration_number: form.value.registration_number, scheme_operator: form.value.scheme_operator || null, registered_on: toIso(form.value.registered_on) },
		{
			preserveScroll: true,
			only: ["exposure"],
			onSuccess: () => (editing.value = null),
			onError: (e) => (errors.value = e),
			onFinish: () => (saving.value = false),
		}
	)
}

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent] focus:!border-[--app-accent]"
</script>

<template>
	<div class="px-4 py-5 space-y-5 text-sm text-gray-700">
		<div v-if="!data" class="space-y-3">
			<div class="h-20 rounded-lg bg-gray-100 animate-pulse" />
			<div class="h-40 rounded-lg bg-gray-100 animate-pulse" />
		</div>

		<div v-else-if="!data.has_data" class="rounded-lg border border-dashed border-gray-300 px-6 py-10 text-center">
			<FontAwesomeIcon :icon="['fal', 'globe-europe']" class="text-2xl text-gray-400" fixed-width aria-hidden="true" />
			<p class="mt-2 font-medium text-gray-900">{{ ctrans("No packaging weights for what was dispatched between :from and :to", { from: date(data.from), to: date(data.to) }) }}</p>
		</div>

		<template v-else>
			<div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
				<div class="rounded-lg ring-1 ring-gray-200 bg-white px-4 py-3">
					<div class="text-xs text-gray-500">{{ ctrans("Sales packaging dispatched") }}</div>
					<div class="text-xl font-semibold text-gray-900 tabular-nums">{{ ctrans(":kg kg", { kg: kg(data.kg) }) }}</div>
				</div>
				<div class="rounded-lg ring-1 ring-gray-200 bg-white px-4 py-3">
					<div class="text-xs text-gray-500">{{ ctrans("To consumers abroad") }}</div>
					<div class="text-xl font-semibold text-gray-900 tabular-nums">{{ ctrans(":kg kg", { kg: kg(data.abroad_consumer_kg) }) }}</div>
					<div class="text-xs text-gray-400">{{ ctrans("Makes us the producer there") }}</div>
				</div>
				<div class="rounded-lg ring-1 ring-gray-200 bg-white px-4 py-3">
					<div class="text-xs text-gray-500">{{ ctrans("To trade customers abroad") }}</div>
					<div class="text-xl font-semibold text-gray-900 tabular-nums">{{ ctrans(":kg kg", { kg: kg(data.abroad_trade_kg) }) }}</div>
					<div class="text-xs text-gray-400">{{ ctrans("They usually report it as importers") }}</div>
				</div>
				<div class="rounded-lg ring-1 bg-white px-4 py-3" :class="data.unregistered ? 'ring-amber-300' : 'ring-gray-200'">
					<div class="text-xs text-gray-500">{{ ctrans("Countries without a registration") }}</div>
					<div class="text-xl font-semibold tabular-nums" :class="data.unregistered ? 'text-amber-600' : 'text-gray-900'">{{ data.unregistered }}</div>
					<div class="text-xs text-gray-400">{{ ctrans("Own country, or consumer parcels sent there") }}</div>
				</div>
			</div>

			<div class="rounded-lg ring-1 ring-gray-200 bg-white overflow-x-auto">
				<table class="min-w-full text-xs">
					<thead class="text-gray-600 whitespace-nowrap">
						<tr class="text-left">
							<th class="px-4 py-2 font-medium">{{ ctrans("Country") }}</th>
							<th class="px-2 py-2 font-medium text-right">{{ ctrans("Consumers kg") }}</th>
							<th class="px-2 py-2 font-medium text-right">{{ ctrans("Trade kg") }}</th>
							<th class="px-2 py-2 font-medium text-right">{{ ctrans("Total kg") }}</th>
							<th class="px-2 py-2 font-medium text-right">{{ ctrans("Share") }}</th>
							<th v-for="month in data.months" :key="month" class="px-2 py-2 font-medium text-right text-gray-400">{{ monthLabel(month) }}</th>
							<th class="px-4 py-2 font-medium">{{ ctrans("Registration") }}</th>
						</tr>
					</thead>
					<tbody class="divide-y divide-gray-100 tabular-nums">
						<tr v-for="row in data.countries" :key="row.country_id">
							<td class="px-4 py-2 whitespace-nowrap">
								<span class="font-medium text-gray-900">{{ row.name }}</span>
								<FontAwesomeIcon v-if="row.own" :icon="['fal', 'home']" class="ml-1 text-gray-400" fixed-width v-tooltip="ctrans('The organisation\'s own country')" />
							</td>
							<td class="px-2 py-2 text-right" :class="row.own ? 'text-gray-400' : 'font-medium text-gray-900'">{{ kg(row.consumer_kg) }}</td>
							<td class="px-2 py-2 text-right">{{ kg(row.trade_kg) }}</td>
							<td class="px-2 py-2 text-right">{{ kg(row.kg) }}</td>
							<td class="px-2 py-2 text-right text-gray-500">{{ locale.number(row.share) }}%</td>
							<td v-for="month in data.months" :key="month" class="px-2 py-2 text-right text-gray-500">{{ row.months[month] ? kg(row.months[month]) : "—" }}</td>
							<td class="px-4 py-2 whitespace-nowrap">
								<span v-if="!row.has_scheme" class="text-gray-400">{{ ctrans("No scheme") }}</span>
								<template v-else>
									<span v-if="row.registration" class="text-gray-900" v-tooltip="ctrans('Recorded by :user on :date', { user: row.registration.recorded_by ?? '', date: date(row.registration.recorded_at) })">
										{{ row.registration.registration_number }}<span v-if="row.registration.scheme_operator" class="text-gray-500"> · {{ row.registration.scheme_operator }}</span>
									</span>
									<span v-else-if="row.unregistered" class="text-amber-600">
										<FontAwesomeIcon :icon="['fal', 'exclamation-triangle']" fixed-width aria-hidden="true" />
										{{ ctrans("Not registered") }}
									</span>
									<span v-else class="text-gray-400">—</span>
									<button type="button" class="ml-2 inline-flex h-6 w-6 items-center justify-center rounded text-gray-400 hover:bg-gray-100 hover:text-gray-700" :aria-label="ctrans('Record registration')" v-tooltip="ctrans('Record registration')" @click="edit(row)">
										<FontAwesomeIcon :icon="['fal', 'pencil']" fixed-width aria-hidden="true" />
									</button>
								</template>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<ul class="space-y-1.5 text-xs text-gray-500">
				<li>{{ ctrans("Sales packaging of what was dispatched, by the country it was delivered to. Consumer parcels are dropshipping, retail and marketplace orders; the rest went to trade customers.") }}</li>
				<li>{{ ctrans("Shipment packaging and SKOs without packaging weights are not included; the Completeness tab lists the SKOs to weigh.") }}</li>
				<li v-if="data.unknown_country_kg > 0">{{ ctrans(":kg kg went to deliveries without a country and is only in the total.", { kg: kg(data.unknown_country_kg) }) }}</li>
				<li>{{ ctrans("Who is obligated in each country needs a compliance adviser's view before registering.") }}</li>
			</ul>
		</template>

		<Dialog :visible="!!editing" modal :header="ctrans('Registration · :country', { country: editing?.name ?? '' })" class="w-full max-w-md" @update:visible="(value) => !value && (editing = null)">
			<div class="space-y-3 text-sm">
				<label class="block space-y-1">
					<span class="text-xs text-gray-500">{{ ctrans("Register number") }}</span>
					<InputText v-model="form.registration_number" class="w-full !py-1 !text-xs" :class="fieldFocusClass" :invalid="!!errors.registration_number" />
				</label>
				<label class="block space-y-1">
					<span class="text-xs text-gray-500">{{ ctrans("Scheme or compliance operator") }}</span>
					<InputText v-model="form.scheme_operator" class="w-full !py-1 !text-xs" :class="fieldFocusClass" />
				</label>
				<label class="block space-y-1">
					<span class="text-xs text-gray-500">{{ ctrans("Registered on") }}</span>
					<DatePicker v-model="form.registered_on" dateFormat="d M yy" :manualInput="false" showIcon iconDisplay="input" class="w-full" :class="fieldFocusClass" :pt="{ pcInputText: { root: { class: '!py-1 !text-xs' } } }" />
				</label>
				<div v-if="Object.keys(errors).length" class="text-xs text-red-600">{{ Object.values(errors).join(" ") }}</div>
				<div class="flex justify-end gap-2">
					<Button v-if="editing?.registration" type="tertiary" :label="ctrans('Remove')" :loading="saving" @click="save(true)">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-trash-alt" class="text-red-500" fixed-width aria-hidden="true" />
						</template>
					</Button>
					<Button type="tertiary" :label="ctrans('Save')" :loading="saving" :disabled="!form.registration_number" @click="save()">
						<template #icon>
							<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
						</template>
					</Button>
				</div>
			</div>
		</Dialog>
	</div>
</template>
