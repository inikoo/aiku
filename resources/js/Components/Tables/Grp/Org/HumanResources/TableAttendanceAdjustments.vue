<script setup lang="ts">
import { ref } from "vue"
import { router } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import { useLocaleStore } from "@/Stores/locale"
import { useFormatTime } from "@/Composables/useFormatTime"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { ctrans } from "@/Composables/useTrans"
import Button from "@/Components/Elements/Buttons/Button.vue"
import AttendanceAdjustmentDialog from "@/Components/HumanResources/AttendanceAdjustmentDialog.vue"
import { faPlus, faCheckCircle, faTimesCircle, faClock, faTimes } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"

library.add(faPlus, faCheckCircle, faTimesCircle, faClock, faTimes)

defineProps<{
	data: {}
	tab?: string
	organisation?: string | null
}>()

const locale = useLocaleStore()
const isCreateModalOpen = defineModel<boolean>("isRequestAdjustmentModalOpen", { default: false })
const cancellingId = ref<number | null>(null)

const statusColors: Record<string, string> = {
	pending: "bg-yellow-100 text-yellow-800 border-yellow-200",
	approved: "bg-green-100 text-green-800 border-green-200",
	rejected: "bg-red-100 text-red-800 border-red-200",
}

const statusIcons: Record<string, string> = {
	pending: "fal fa-clock",
	approved: "fal fa-check-circle",
	rejected: "fal fa-times-circle",
}

const formatTime = (dateString: string | null) => {
	if (!dateString) return "–"
	const date = new Date(dateString)
	return date.toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit", hour12: false })
}

const cancelAdjustment = (adjustmentId: number) => {
	router.delete(route("grp.clocking_employees.adjustments.cancel", [adjustmentId]), {
		preserveScroll: true,
		onStart: () => (cancellingId.value = adjustmentId),
		onFinish: () => (cancellingId.value = null),
	})
}
</script>

<template>
	<div class="px-4 py-4 space-y-4">
		<div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
			<p class="text-sm text-gray-600">
				{{
					ctrans(
						"For a day with no clockings at all, request the missing check-in, check-out or both here. To correct a day that already has clockings, use Request correction on the Timesheets tab. Requests can go back one month and apply once they are approved."
					)
				}}
			</p>
		</div>


		<Table :resource="data" :name="tab">
			<template #cell(date)="{ item: adjustment }">
				<span class="text-gray-900">{{
					useFormatTime(adjustment.date, { localeCode: locale.language.code })
				}}</span>
			</template>

			<template #cell(original_times)="{ item: adjustment }">
				<span v-if="adjustment.original_start_at || adjustment.original_end_at" class="text-gray-600 tabular-nums">
					{{ formatTime(adjustment.original_start_at) }} → {{ formatTime(adjustment.original_end_at) }}
				</span>
				<span v-else class="text-gray-400">{{ ctrans("No clockings") }}</span>
			</template>

			<template #cell(requested_times)="{ item: adjustment }">
				<span class="text-gray-900 tabular-nums font-medium">
					{{ formatTime(adjustment.requested_start_at) }} → {{ formatTime(adjustment.requested_end_at) }}
				</span>
			</template>

			<template #cell(status_label)="{ item: adjustment }">
				<span
					class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium border"
					:class="statusColors[adjustment.status]">
					<FontAwesomeIcon :icon="statusIcons[adjustment.status]" fixed-width />
					{{ adjustment.status_label }}
				</span>
			</template>

			<template #cell(reason)="{ item: adjustment }">
				<div class="max-w-xs">
					<span class="text-gray-600 text-sm truncate block">{{ adjustment.reason }}</span>
					<span
						v-if="adjustment.status === 'rejected' && adjustment.approval_comment"
						class="text-red-600 text-xs truncate block mt-1">
						{{ adjustment.approval_comment }}
					</span>
				</div>
			</template>

			<template #cell(actions)="{ item: adjustment }">
				<Button
					v-if="adjustment.status === 'pending'"
					type="tertiary"
					size="xs"
					:label="ctrans('Cancel request')"
					:loading="cancellingId === adjustment.id"
					@click="cancelAdjustment(adjustment.id)">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</template>
		</Table>

		<AttendanceAdjustmentDialog v-model:visible="isCreateModalOpen" :organisation="organisation" />
	</div>
</template>
