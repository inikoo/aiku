<script setup lang="ts">
import { Head } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCoins, faFileExcel } from "@fal"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import DatePicker from "primevue/datepicker"
import { computed, ref } from "vue"

library.add(faCoins, faFileExcel)

const props = defineProps<{
	title: string
	pageHead: object
	downloadRoute: { name: string; parameters: Record<string, string> }
}>()

const today = new Date()
const financialYearStartYear = today.getMonth() >= 8 ? today.getFullYear() - 1 : today.getFullYear() - 2
const startDate = ref<Date>(new Date(financialYearStartYear, 8, 1))
const endDate = ref<Date>(new Date(financialYearStartYear + 1, 7, 31))

const isoDate = (date: Date) =>
	`${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`

const downloadUrl = computed(() =>
	route(props.downloadRoute.name, {
		...props.downloadRoute.parameters,
		start_date: isoDate(startDate.value),
		end_date: isoDate(endDate.value),
	})
)

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"
const datePickerPt = { pcInputText: { root: { class: "!w-36 !py-1 !text-sm" } } }
</script>

<template>
	<Head :title="title" />
	<PageHeading :data="pageHead" />

	<div class="max-w-4xl px-4 py-6 space-y-4">
		<p class="text-sm text-gray-600">
			{{ ctrans("Cost of the stock picked in the period, by month, shop and stock family, beside the invoiced revenue of those stock lines. Picks imported from Aurora have no delivery note, so their cost is split across shops by that stock's invoiced revenue in the month; the last column shows how much of each row came from that split.") }}
		</p>

		<div class="flex flex-wrap items-end gap-3">
			<label class="text-xs text-gray-600">
				<span class="block mb-1">{{ ctrans("From") }}</span>
				<DatePicker v-model="startDate" :maxDate="endDate" dateFormat="dd M yy" :manualInput="false" showIcon iconDisplay="input" :class="fieldFocusClass" :pt="datePickerPt" />
			</label>
			<label class="text-xs text-gray-600">
				<span class="block mb-1">{{ ctrans("To") }}</span>
				<DatePicker v-model="endDate" :minDate="startDate" dateFormat="dd M yy" :manualInput="false" showIcon iconDisplay="input" :class="fieldFocusClass" :pt="datePickerPt" />
			</label>
			<a :href="downloadUrl">
				<Button type="tertiary" :label="ctrans('Download Excel')">
					<template #icon>
						<FontAwesomeIcon :icon="faFileExcel" class="text-green-600" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</a>
		</div>
	</div>
</template>
