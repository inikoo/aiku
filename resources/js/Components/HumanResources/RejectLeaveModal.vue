<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { router } from "@inertiajs/vue3"
import Dialog from "primevue/dialog"
import Textarea from "primevue/textarea"
import { notify } from "@kyvg/vue3-notification"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faTimes, faCheck } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { inputFocusClass } from "@/Composables/useHrFormControls"
import { routeType } from "@/types/route"

library.add(faTimes, faCheck)

const props = defineProps<{
	isOpen: boolean
	leave: any
	route: routeType
}>()

const emit = defineEmits<{
	(e: "close"): void
}>()

const maxReasonLength = 255
const rejectionReason = ref("")
const isLoading = ref(false)
const errors = ref<Record<string, string>>({})

watch(
	() => props.isOpen,
	() => {
		rejectionReason.value = ""
		errors.value = {}
	}
)

const leavePeriod = computed(() => {
	if (!props.leave?.start_date) {
		return ""
	}

	const start = useFormatTime(props.leave.start_date)
	const end = props.leave.end_date && props.leave.end_date !== props.leave.start_date ? useFormatTime(props.leave.end_date) : null

	return end ? `${start} – ${end}` : start
})

const closeModal = () => {
	emit("close")
}

const submitReject = () => {
	if (!rejectionReason.value.trim()) {
		errors.value = { rejection_reason: ctrans("Please enter a rejection reason") }

		return
	}

	router.post(
		route(props.route.name, props.route.parameters),
		{ rejection_reason: rejectionReason.value },
		{
			preserveScroll: true,
			onStart: () => {
				isLoading.value = true
				errors.value = {}
			},
			onError: (responseErrors: Record<string, string>) => {
				errors.value = responseErrors

				if (!responseErrors.rejection_reason && !responseErrors.status) {
					notify({
						title: ctrans("Something went wrong"),
						text: Object.values(responseErrors)[0] ?? "",
						type: "error",
					})
				}
			},
			onSuccess: () => {
				emit("close")
			},
			onFinish: () => {
				isLoading.value = false
			},
		}
	)
}
</script>

<template>
	<Dialog
		:visible="isOpen"
		modal
		:draggable="false"
		:header="ctrans('Reject Leave Request')"
		:style="{ width: '30rem', maxWidth: 'calc(100vw - 2rem)' }"
		@update:visible="(isVisible: boolean) => !isVisible && closeModal()">
		<form class="space-y-4" @submit.prevent="submitReject">
			<div v-if="leave" class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
				<div class="font-medium text-gray-900">{{ leave.employee_name }}</div>
				<div class="tabular-nums text-gray-600">
					<template v-if="leave.type_label">{{ leave.type_label }} <span class="text-gray-300">·</span> </template>{{ leavePeriod }}
				</div>
			</div>

			<div>
				<label for="reject-leave-reason" class="mb-1 block text-sm font-medium text-gray-700">
					{{ ctrans("Rejection Reason") }} <span class="text-red-500">*</span>
				</label>
				<Textarea
					v-model="rejectionReason"
					id="reject-leave-reason"
					rows="3"
					autoResize
					fluid
					:maxlength="maxReasonLength"
					:invalid="!!errors.rejection_reason"
					:placeholder="ctrans('Enter the reason for rejection')"
					:class="inputFocusClass" />
				<div class="mt-1 flex items-start justify-between gap-2">
					<p class="text-sm text-red-500">{{ errors.rejection_reason ?? errors.status ?? "" }}</p>
					<span class="shrink-0 text-xs tabular-nums text-gray-400">{{ rejectionReason.length }}/{{ maxReasonLength }}</span>
				</div>
			</div>

			<div class="flex justify-end gap-2 pt-2">
				<Button type="tertiary" :label="ctrans('Cancel')" :disabled="isLoading" @click.prevent="closeModal">
					<template #icon>
						<FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
				<Button type="tertiary" nativeType="submit" :label="ctrans('Reject')" :loading="isLoading" :disabled="!rejectionReason.trim()">
					<template #icon>
						<FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>
		</form>
	</Dialog>
</template>
