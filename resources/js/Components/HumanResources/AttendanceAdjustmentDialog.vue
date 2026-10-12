<script setup lang="ts">
import { ref, computed, watch } from "vue"
import { useForm } from "@inertiajs/vue3"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { useLocaleStore } from "@/Stores/locale"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Dialog from "primevue/dialog"
import DatePicker from "primevue/datepicker"
import Textarea from "primevue/textarea"
import FileUpload from "primevue/fileupload"
import { faClock, faTimes, faCheck, faPaperclip, faFileAlt } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"

library.add(faClock, faTimes, faCheck, faPaperclip, faFileAlt)

const props = defineProps<{
	organisation?: string | null
	timesheet?: {
		id: number
		date: string
		start_time_local: string | null
		end_time_local: string | null
	} | null
}>()

const visible = defineModel<boolean>("visible", { default: false })

const locale = useLocaleStore()
const isSubmitting = ref(false)

const maxAttachments = 3
const maxAttachmentSize = 5 * 1024 * 1024

const adjustmentDate = ref<Date | null>(null)
const requestedStartTime = ref<Date | null>(null)
const requestedEndTime = ref<Date | null>(null)
const fileUpload = ref<InstanceType<typeof FileUpload> | null>(null)

const adjustmentForm = useForm({
	date: "",
	requested_start_at: "",
	requested_end_at: "",
	reason: "",
	attachments: [] as File[],
})

const today = new Date()
const earliestDate = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate())

const title = computed(() =>
	props.timesheet ? ctrans("Request timesheet correction") : ctrans("Clocking adjustment")
)

const twoDigits = (value: number) => String(value).padStart(2, "0")
const toDateString = (value: Date | null) =>
	value ? `${value.getFullYear()}-${twoDigits(value.getMonth() + 1)}-${twoDigits(value.getDate())}` : ""
const toTimeString = (value: Date | null) =>
	value ? `${twoDigits(value.getHours())}:${twoDigits(value.getMinutes())}` : ""
const toTimeDate = (value: string | null | undefined) => {
	if (!value) return null
	const [hours, minutes] = value.split(":").map(Number)
	const time = new Date()
	time.setHours(hours, minutes, 0, 0)
	return time
}

watch(adjustmentDate, (value) => {
	adjustmentForm.date = toDateString(value)
	adjustmentForm.clearErrors("date")
})
watch(requestedStartTime, (value) => {
	adjustmentForm.requested_start_at = toTimeString(value)
	adjustmentForm.clearErrors("requested_start_at", "requested_end_at")
})
watch(requestedEndTime, (value) => {
	adjustmentForm.requested_end_at = toTimeString(value)
	adjustmentForm.clearErrors("requested_start_at", "requested_end_at")
})
watch(
	() => adjustmentForm.reason,
	() => adjustmentForm.clearErrors("reason")
)

const resetAdjustmentForm = () => {
	adjustmentDate.value = null
	requestedStartTime.value = null
	requestedEndTime.value = null
	fileUpload.value?.clear()
	adjustmentForm.reset()
	adjustmentForm.clearErrors()
}

watch(visible, (isVisible) => {
	if (!isVisible || !props.timesheet) return
	requestedStartTime.value = toTimeDate(props.timesheet.start_time_local)
	requestedEndTime.value = toTimeDate(props.timesheet.end_time_local)
})

const onAttachmentsChanged = (event: { files: File[] }) => {
	adjustmentForm.attachments = [...event.files]
	adjustmentForm.clearErrors("attachments")
}

const formatFileSize = (bytes: number) =>
	bytes < 1024 * 1024 ? `${Math.ceil(bytes / 1024)} KB` : `${(bytes / 1024 / 1024).toFixed(1)} MB`

const submitAdjustment = () => {
	isSubmitting.value = true
	adjustmentForm
		.transform((data) => ({
			...data,
			date: props.timesheet ? String(props.timesheet.date).slice(0, 10) : data.date,
			timesheet_id: props.timesheet?.id ?? undefined,
			organisation: props.organisation ?? undefined,
		}))
		.post(route("grp.clocking_employees.adjustments.store"), {
			preserveScroll: true,
			forceFormData: true,
			onSuccess: () => {
				visible.value = false
			},
			onFinish: () => {
				isSubmitting.value = false
			},
		})
}

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"
const fileUploadPt = {
	root: { class: "!border-0 !bg-transparent" },
	header: { class: "!p-0 !border-0 !bg-transparent" },
	content: { class: "!p-0 !pt-2 !border-0 !bg-transparent" },
}
</script>

<template>
	<Dialog
		v-model:visible="visible"
		modal
		:draggable="false"
		:header="title"
		:style="{ width: '32rem', maxWidth: 'calc(100vw - 2rem)' }"
		@hide="resetAdjustmentForm">
		<form @submit.prevent="submitAdjustment" class="space-y-4">
			<div v-if="timesheet" class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
				<div class="font-medium text-gray-900">
					{{ useFormatTime(timesheet.date, { localeCode: locale.language.code }) }}
				</div>
				<div class="text-gray-600 tabular-nums">
					{{ ctrans("Recorded") }}: {{ timesheet.start_time_local ?? "–" }} → {{ timesheet.end_time_local ?? "–" }}
				</div>
				<p v-if="adjustmentForm.errors.date" class="text-sm text-red-500 mt-1">
					{{ adjustmentForm.errors.date }}
				</p>
			</div>

			<div v-else>
				<label for="adjustment-date" class="block text-sm font-medium text-gray-700 mb-1">
					{{ ctrans("Date") }} <span class="text-red-500">*</span>
				</label>
				<DatePicker
					v-model="adjustmentDate"
					inputId="adjustment-date"
					dateFormat="d M yy"
					:minDate="earliestDate"
					:maxDate="today"
					:manualInput="false"
					:invalid="!!adjustmentForm.errors.date"
					:placeholder="ctrans('A day with no clockings')"
					showIcon
					iconDisplay="input"
					fluid
					:class="fieldFocusClass" />
				<p v-if="adjustmentForm.errors.date" class="text-sm text-red-500 mt-1">
					{{ adjustmentForm.errors.date }}
				</p>
			</div>

			<div>
				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
					<div>
						<label for="adjustment-start" class="block text-sm font-medium text-gray-700 mb-1">
							{{ ctrans("Check-in time") }}
						</label>
						<DatePicker
							v-model="requestedStartTime"
							inputId="adjustment-start"
							timeOnly
							hourFormat="24"
							:showClear="!timesheet"
							:invalid="!!adjustmentForm.errors.requested_start_at"
							placeholder="08:00"
							showIcon
							iconDisplay="input"
							fluid
							:class="fieldFocusClass">
							<template #inputicon="{ clickCallback }">
								<FontAwesomeIcon icon="fal fa-clock" class="text-gray-400 cursor-pointer" fixed-width aria-hidden="true" @click="clickCallback" />
							</template>
						</DatePicker>
						<p v-if="adjustmentForm.errors.requested_start_at" class="text-sm text-red-500 mt-1">
							{{ adjustmentForm.errors.requested_start_at }}
						</p>
					</div>

					<div>
						<label for="adjustment-end" class="block text-sm font-medium text-gray-700 mb-1">
							{{ ctrans("Check-out time") }}
						</label>
						<DatePicker
							v-model="requestedEndTime"
							inputId="adjustment-end"
							timeOnly
							hourFormat="24"
							:showClear="!timesheet"
							:invalid="!!adjustmentForm.errors.requested_end_at"
							placeholder="17:00"
							showIcon
							iconDisplay="input"
							fluid
							:class="fieldFocusClass">
							<template #inputicon="{ clickCallback }">
								<FontAwesomeIcon icon="fal fa-clock" class="text-gray-400 cursor-pointer" fixed-width aria-hidden="true" @click="clickCallback" />
							</template>
						</DatePicker>
						<p v-if="adjustmentForm.errors.requested_end_at" class="text-sm text-red-500 mt-1">
							{{ adjustmentForm.errors.requested_end_at }}
						</p>
					</div>
				</div>
				<p class="text-xs text-gray-500 mt-1">
					{{ ctrans("Fill in check-in, check-out, or both.") }}
				</p>
			</div>

			<div>
				<label for="adjustment-reason" class="block text-sm font-medium text-gray-700 mb-1">
					{{ ctrans("Reason") }} <span class="text-red-500">*</span>
				</label>
				<Textarea
					v-model="adjustmentForm.reason"
					id="adjustment-reason"
					rows="3"
					autoResize
					fluid
					maxlength="1000"
					:invalid="!!adjustmentForm.errors.reason"
					:placeholder="ctrans('Please explain what happened')"
					class="focus:!border-[--app-accent]" />
				<p v-if="adjustmentForm.errors.reason" class="text-sm text-red-500 mt-1">
					{{ adjustmentForm.errors.reason }}
				</p>
			</div>

			<div>
				<span class="block text-sm font-medium text-gray-700 mb-1">
					{{ ctrans("Supporting Document (Optional)") }}
				</span>
				<FileUpload
					ref="fileUpload"
					multiple
					customUpload
					accept=".pdf,.jpg,.jpeg,.png"
					:maxFileSize="maxAttachmentSize"
					:fileLimit="maxAttachments"
					:pt="fileUploadPt"
					@select="onAttachmentsChanged"
					@remove="onAttachmentsChanged">
					<template #header="{ chooseCallback }">
						<Button type="tertiary" :label="ctrans('Choose file')" @click="chooseCallback">
							<template #icon>
								<FontAwesomeIcon icon="fal fa-paperclip" class="text-[--app-accent]" fixed-width aria-hidden="true" />
							</template>
						</Button>
					</template>
					<template #content="{ files, removeFileCallback }">
						<ul v-if="files.length" class="divide-y divide-gray-100 rounded-lg border border-gray-200">
							<li v-for="(file, index) in files" :key="file.name + file.size" class="flex items-center gap-2 px-3 py-2 text-sm">
								<FontAwesomeIcon icon="fal fa-file-alt" class="text-gray-400" fixed-width aria-hidden="true" />
								<span class="truncate text-gray-900">{{ file.name }}</span>
								<span class="ml-auto shrink-0 text-xs text-gray-500 tabular-nums">{{ formatFileSize(file.size) }}</span>
								<button type="button" class="shrink-0 text-gray-400 hover:text-red-500" :aria-label="ctrans('Remove')" @click="removeFileCallback(index)">
									<FontAwesomeIcon icon="fal fa-times" fixed-width aria-hidden="true" />
								</button>
							</li>
						</ul>
					</template>
					<template #empty>
						<p class="rounded-lg border border-dashed border-gray-300 px-3 py-4 text-center text-xs text-gray-500">
							{{ ctrans("Drop files here, or use the button above. PDF, JPG or PNG, up to :count files of 5 MB each.", { count: maxAttachments }) }}
						</p>
					</template>
				</FileUpload>
				<p v-if="adjustmentForm.errors.attachments" class="text-sm text-red-500 mt-1">
					{{ adjustmentForm.errors.attachments }}
				</p>
			</div>

			<div class="flex justify-end gap-2 pt-2">
				<Button type="tertiary" :label="ctrans('Cancel')" @click="visible = false">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
				<Button type="tertiary" nativeType="submit" :label="ctrans('Submit Request')" :loading="isSubmitting">
					<template #icon>
						<FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>
		</form>
	</Dialog>
</template>
