<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3"
import { computed, ref } from "vue"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Table from "@/Components/Table/Table.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Tag from "@/Components/Tag.vue"
import EmployeeAvatar from "@/Components/HumanResources/EmployeeAvatar.vue"
import Dialog from "primevue/dialog"
import Select from "primevue/select"
import { useFormatTime } from "@/Composables/useFormatTime"
import { useLocaleStore } from "@/Stores/locale"
import { capitalize } from "@/Composables/capitalize"
import { PageHeadingTypes } from "@/types/PageHeading"
import { ctrans } from "@/Composables/useTrans"
import { coveredByExplanation, isOnLeaveDuring } from "@/Composables/useLeaveCovers"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faUserFriends, faUserTimes, faEdit, faPlus, faInfoCircle, faTimes, faCheck, faExclamationTriangle } from "@fal"

library.add(faUserFriends, faUserTimes, faEdit, faPlus, faInfoCircle, faTimes, faCheck, faExclamationTriangle)

type CoverStatus = "ongoing" | "upcoming" | "ended"

interface LeaveCover {
	id: number
	employee_id: number
	employee_name: string
	employee_avatar: string | null
	type_label: string
	type_color: string
	start_date: string
	end_date: string
	status: CoverStatus
	is_ongoing: boolean
	has_ended: boolean
	cover_employee_id: number | null
	covered_by: string | null
	covered_by_avatar: string | null
}

const props = defineProps<{
	title: string
	pageHead: PageHeadingTypes
	data: object
	can_edit: boolean
	employee_options: Record<string, string>
	employee_leave_periods: Record<string, [string, string][]>
}>()

const locale = useLocaleStore()
const selectedLeave = ref<LeaveCover | null>(null)

const isCoverDialogVisible = computed({
	get: () => !!selectedLeave.value,
	set: (visible: boolean) => {
		if (!visible) {
			selectedLeave.value = null
		}
	},
})

const statusPills: Record<CoverStatus, { label: string; class: string; dot: string }> = {
	ongoing: { label: ctrans("Now"), class: "bg-green-100 text-green-800 ring-green-200", dot: "bg-green-500" },
	upcoming: { label: ctrans("Upcoming"), class: "bg-amber-100 text-amber-800 ring-amber-200", dot: "bg-amber-500" },
	ended: { label: ctrans("Ended"), class: "bg-gray-100 text-gray-600 ring-gray-200", dot: "bg-gray-400" },
}

const typeTagStyle = (color: string) => ({
	backgroundColor: `color-mix(in srgb, ${color} 12%, white)`,
	borderColor: `color-mix(in srgb, ${color} 35%, white)`,
	color: `color-mix(in srgb, ${color} 75%, black)`,
})

const coverOptions = computed(() =>
	Object.entries(props.employee_options ?? {})
		.map(([value, label]) => ({
			value: Number(value),
			label,
			disabled: isOnLeaveDuring(props.employee_leave_periods?.[value], selectedLeave.value?.start_date ?? "", selectedLeave.value?.end_date ?? ""),
		}))
		.filter((option) => option.value !== selectedLeave.value?.employee_id)
		.sort((a, b) => Number(a.disabled) - Number(b.disabled))
)

const coverForm = useForm({
	cover_employee_id: "" as string | number,
})

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent]"

const formatDate = (date: string) => useFormatTime(date, { localeCode: locale?.language?.code })

const openCoverModal = (leave: LeaveCover) => {
	selectedLeave.value = leave
	coverForm.clearErrors()
	coverForm.cover_employee_id = leave.cover_employee_id ?? ""
}

const closeCoverModal = () => {
	selectedLeave.value = null
}

const saveCover = (leave: LeaveCover, coverEmployeeId: string | number) => {
	coverForm
		.transform((data) => ({ ...data, cover_employee_id: coverEmployeeId }))
		.patch(route("grp.org.hr.leaves.cover.update", { organisation: route().params.organisation, leave: leave.id }), {
			preserveScroll: true,
			onSuccess: closeCoverModal,
		})
}
</script>

<template>
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead" />

	<p class="px-4 pt-3 text-sm text-gray-500">
		{{ ctrans("Who stands in for each employee on approved leave. Assign a colleague so their work is not left unattended while they are away.") }}
	</p>

	<Table :resource="data" class="mt-3">
		<template #cell(employee_name)="{ item: leave }">
			<span class="inline-flex items-center gap-2">
				<EmployeeAvatar :name="leave.employee_name" :image="leave.employee_avatar" size="sm" />
				{{ leave.employee_name }}
			</span>
		</template>

		<template #cell(type_label)="{ item: leave }">
			<span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded border px-1.5 py-0.5 text-xs font-medium" :style="typeTagStyle(leave.type_color)">
				<span class="h-1.5 w-1.5 rounded-full" :style="{ backgroundColor: leave.type_color }" aria-hidden="true" />
				{{ leave.type_label }}
			</span>
		</template>

		<template #cell(status)="{ item: leave }">
			<span
				v-if="statusPills[leave.status as CoverStatus]"
				class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
				:class="statusPills[leave.status as CoverStatus].class">
				<span class="h-1.5 w-1.5 rounded-full" :class="statusPills[leave.status as CoverStatus].dot" aria-hidden="true" />
				{{ statusPills[leave.status as CoverStatus].label }}
			</span>
		</template>

		<template #cell(start_date)="{ item: leave }">
			<span class="whitespace-nowrap tabular-nums">{{ formatDate(leave.start_date) }}</span>
		</template>

		<template #cell(end_date)="{ item: leave }">
			<span class="whitespace-nowrap tabular-nums">{{ formatDate(leave.end_date) }}</span>
		</template>

		<template #cell(covered_by)="{ item: leave }">
			<span v-if="leave.covered_by" class="inline-flex items-center gap-2 font-medium text-gray-800">
				<EmployeeAvatar :name="leave.covered_by" :image="leave.covered_by_avatar" size="sm" />
				{{ leave.covered_by }}
			</span>
			<span
				v-else-if="leave.has_ended"
				class="text-gray-400">
				—
			</span>
			<span
				v-else
				class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
				:class="leave.is_ongoing ? 'bg-red-100 text-red-800 ring-red-200' : 'bg-amber-100 text-amber-800 ring-amber-200'">
				<FontAwesomeIcon :icon="faExclamationTriangle" fixed-width aria-hidden="true" />
				{{ ctrans("Not covered") }}
			</span>
		</template>

		<template #cell(actions)="{ item: leave }">
			<div v-if="can_edit && !leave.has_ended" class="flex flex-wrap justify-end gap-2">
				<Button
					:label="leave.covered_by ? ctrans('Change') : ctrans('Assign cover')"
					type="tertiary"
					size="xs"
					class="whitespace-nowrap"
					@click="openCoverModal(leave)">
					<template #icon>
						<FontAwesomeIcon :icon="leave.covered_by ? faEdit : faPlus" class="text-[--app-accent]" fixed-width aria-hidden="true" />
					</template>
				</Button>
				<Button
					v-if="leave.covered_by"
					:label="ctrans('End cover')"
					type="tertiary"
					size="xs"
					class="whitespace-nowrap"
					:disabled="coverForm.processing"
					@click="saveCover(leave, '')">
					<template #icon>
						<FontAwesomeIcon :icon="faUserTimes" class="text-red-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>
		</template>
	</Table>

	<Dialog
		v-model:visible="isCoverDialogVisible"
		modal
		:draggable="false"
		:header="selectedLeave ? ctrans('Cover for :name', { name: selectedLeave.employee_name }) : ''"
		:style="{ width: '32rem', maxWidth: 'calc(100vw - 2rem)' }">
		<form v-if="selectedLeave" class="space-y-4" @submit.prevent="saveCover(selectedLeave, coverForm.cover_employee_id)">
			<div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
				<div class="font-medium text-gray-900">{{ selectedLeave.type_label }}</div>
				<div class="text-gray-600 tabular-nums">{{ formatDate(selectedLeave.start_date) }} – {{ formatDate(selectedLeave.end_date) }}</div>
			</div>

			<div>
				<label for="cover-employee" class="mb-1 flex items-center gap-1 text-sm font-medium text-gray-700">
					{{ ctrans("Covered by") }}
					<FontAwesomeIcon v-tooltip="coveredByExplanation()" :icon="faInfoCircle" class="text-gray-400 hover:text-gray-600" fixed-width :aria-label="coveredByExplanation()" />
				</label>
				<Select
					v-model="coverForm.cover_employee_id"
					inputId="cover-employee"
					:options="coverOptions"
					optionLabel="label"
					optionValue="value"
					optionDisabled="disabled"
					filter
					fluid
					:invalid="!!coverForm.errors.cover_employee_id"
					:placeholder="ctrans('Select employee')"
					:class="fieldFocusClass">
					<template #option="{ option }">
						<div class="flex w-full items-center justify-between gap-2">
							<span>{{ option.label }}</span>
							<Tag v-if="option.disabled" size="xxs" :label="ctrans('On leave')" />
						</div>
					</template>
				</Select>
				<p v-if="coverForm.errors.cover_employee_id" class="mt-1 text-sm text-red-500">{{ coverForm.errors.cover_employee_id }}</p>
			</div>

			<p class="text-sm text-gray-500">{{ ctrans("The cover gets this employee's permissions until the leave ends") }}</p>

			<div class="flex justify-end gap-2 pt-2">
				<Button type="tertiary" :label="ctrans('Cancel')" @click="closeCoverModal">
					<template #icon>
						<FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
				<Button type="tertiary" nativeType="submit" :label="ctrans('Save')" :loading="coverForm.processing" :disabled="!coverForm.cover_employee_id">
					<template #icon>
						<FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>
		</form>
	</Dialog>
</template>
