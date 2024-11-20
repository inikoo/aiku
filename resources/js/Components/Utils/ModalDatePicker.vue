<script setup lang="ts">
import { ref, reactive } from "vue"
import Modal from "@/Components/Utils/Modal.vue"
import DatePicker from "primevue/datepicker"
import Button from "@/Components/Elements/Buttons/Button.vue"

const props = defineProps<{
	modelValue: boolean
}>()

const emit = defineEmits(["update:modelValue", "date-range-selected"])

// Reactive state
const selectedOption = ref("last7days") 
const customDateRange = reactive({
	startDate: null as Date | null,
	endDate: null as Date | null,
})

const predefinedRanges = [
	{ label: "Most recent date", value: "mostRecent" },
	{ label: "Last 7 days", value: "last7days" },
	{ label: "Last 28 days", value: "last28days" },
	{ label: "Last 3 months", value: "last3months" },
	{ label: "Last 6 months", value: "last6months" },
	{ label: "Last 12 months", value: "last12months" },
	{ label: "Last 16 months", value: "last16months" },
	{ label: "Custom", value: "custom" },
]

const closeModal = () => {
	emit("update:modelValue", false)
}

// Utility function to calculate date ranges
function calculateDateRange(filter: string): { startDate: Date; endDate: Date } {
	const today = new Date() // Current date
	let startDate = new Date(today) // Clone today's date
	let endDate = new Date(today) // Default endDate is today

	switch (filter) {
		case "mostRecent":
			startDate = endDate // Both start and end are the same
			break
		case "last7days":
			startDate.setDate(today.getDate() - 7)
			break
		case "last28days":
			startDate.setDate(today.getDate() - 28)
			break
		case "last3months":
			startDate.setMonth(today.getMonth() - 3)
			break
		case "last6months":
			startDate.setMonth(today.getMonth() - 6)
			break
		case "last12months":
			startDate.setMonth(today.getMonth() - 12)
			break
		case "last16months":
			startDate.setMonth(today.getMonth() - 16)
			break
		default:
			throw new Error("Invalid filter specified")
	}

	return { startDate, endDate }
}

const applyDateRange = () => {
	let selectedRange

	if (selectedOption.value === "custom") {
		selectedRange = {
			startDate: customDateRange.startDate,
			endDate: customDateRange.endDate,
		}
	} else {
		try {
			selectedRange = calculateDateRange(selectedOption.value)
		} catch (error) {
			console.error(error.message)
			return
		}
	}

	emit("date-range-selected", selectedRange)
	closeModal()
}
</script>

<template>
	<Modal :isOpen="modelValue" :closeButton="true" width="w-[500px]">
		<div class="p-6 space-y-6">
			<h2 class="text-lg font-semibold text-gray-700">Date range</h2>
			<!-- Predefined Options -->
			<div class="space-y-4">
				<div v-for="range in predefinedRanges" :key="range.value" class="flex items-center">
					<input
						type="radio"
						:value="range.value"
						v-model="selectedOption"
						class="mr-4"
						:id="range.value" />
					<label :for="range.value" class="text-sm text-gray-600">{{
						range.label
					}}</label>
				</div>
			</div>
			<!-- Custom Range Selection -->
			<div v-if="selectedOption === 'custom'" class="flex items-center space-x-4 mt-4">
				<div class="w-1/2">
					<label for="start-date" class="block text-sm font-medium text-gray-600"
						>Start Date</label
					>
					<DatePicker
						id="start-date"
						v-model="customDateRange.startDate"
						class="w-full mt-1" />
				</div>
				<div class="w-1/2">
					<label for="end-date" class="block text-sm font-medium text-gray-600"
						>End Date</label
					>
					<DatePicker
						id="end-date"
						v-model="customDateRange.endDate"
						class="w-full mt-1" />
				</div>
			</div>
			<!-- Action Buttons -->
			<div class="flex justify-end space-x-4">
				<Button variant="outline" @click="closeModal">Cancel</Button>
				<Button
					variant="primary"
					:disabled="
						selectedOption === 'custom' &&
						(!customDateRange.startDate || !customDateRange.endDate)
					"
					@click="applyDateRange">
					Apply
				</Button>
			</div>
		</div>
	</Modal>
</template>
