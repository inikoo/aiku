<!--
    * Author: Vika Aqordi
    * Created on: 2026-10-16 16:50
    * Github: https://github.com/aqordeon
    * Copyright: 2026
-->

<script setup lang="ts">
import { ref, watch } from "vue"
import { useForm } from "@inertiajs/vue3"
import Dialog from "primevue/dialog"
import FileUpload from "primevue/fileupload"
import InputNumber from "primevue/inputnumber"
import Textarea from "primevue/textarea"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faCheck, faTimes, faImage, faTrashAlt } from "@fal"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { addPhotos } from "@/Composables/usePhotoSelection"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { routeType } from "@/types/route"

export type CheckableItem = {
	code: string | null
	reference?: string
	received_at?: string
	quantity: number
	quantity_rejected: number | null
	reject_reason: string | null
	units_per_sko?: number
	photos: { id: number; name: string; url: string }[]
	check_route: routeType | null
}

const props = defineProps<{
	item: CheckableItem | null
}>()

const emit = defineEmits<{ close: [] }>()

const locale = useLocaleStore()

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"
const chooseFilePt = { pcChooseButton: { root: { class: "!border !border-gray-300 !bg-white !text-gray-700 hover:!bg-gray-50" } } }

const formatQuantity = (value: number | null) => locale.number(Math.round(Number(value ?? 0) * 1000) / 1000)

const form = useForm<{ quantity_rejected: number | null; reject_reason: string | null; photos: File[] }>({
	quantity_rejected: 0,
	reject_reason: null,
	photos: [],
})

watch(() => props.item, (item) => {
	if (!item) {
		return
	}
	form.clearErrors()
	form.quantity_rejected = item.quantity_rejected ?? 0
	form.reject_reason = item.reject_reason
	form.photos = []
})

const photoUploader = ref<InstanceType<typeof FileUpload> | null>(null)

const removePhoto = (index: number) => {
	form.photos = form.photos.filter((_, position) => position !== index)
}

function submit() {
	const checkRoute = props.item?.check_route
	if (!checkRoute) {
		return
	}

	form
		.transform((data) => ({
			quantity_rejected: data.quantity_rejected ?? 0,
			reject_reason: data.reject_reason || null,
			...(data.photos.length ? { photos: data.photos } : {}),
		}))
		.post(route(checkRoute.name, checkRoute.parameters), {
			forceFormData: true,
			preserveScroll: true,
			onSuccess: () => emit("close"),
		})
}
</script>

<template>
	<Dialog
		:visible="!!item"
		modal
		:header="ctrans('Check :code', { code: item?.code ?? '' })"
		:style="{ width: '30rem', maxWidth: 'calc(100vw - 2rem)' }"
		:draggable="false"
		@update:visible="(visible) => !visible && emit('close')"
	>
		<div v-if="item" class="space-y-3 text-sm">
			<p class="text-gray-600">
				<span v-if="item.reference" class="whitespace-nowrap font-medium text-gray-800">{{ item.reference }}</span>
				{{ ctrans(":units units were received. Enter how many are rejected; enter 0 when all are good.", { units: formatQuantity(item.quantity) }) }}
			</p>
			<label class="block space-y-1">
				<span class="text-xs text-gray-500">{{ ctrans("Units rejected") }}</span>
				<InputNumber v-model="form.quantity_rejected" :min="0" :max="item.quantity" :maxFractionDigits="3" :class="fieldFocusClass" fluid />
				<span class="block text-xs text-gray-500">
					<template v-if="item.units_per_sko">{{ ctrans("= :skos SKO.", { skos: formatQuantity(Number(form.quantity_rejected ?? 0) / item.units_per_sko) }) }}</template>
					{{ ctrans("Rejected units go back to In production: the supplier owes them again.") }}
				</span>
			</label>
			<label class="block space-y-1">
				<span class="text-xs text-gray-500">{{ ctrans("Reason (optional)") }}</span>
				<Textarea v-model="form.reject_reason" rows="2" autoResize fluid :class="fieldFocusClass" :placeholder="ctrans('Printed in the remarks of the receipt note')" />
			</label>
			<div class="space-y-1">
				<span class="block text-xs text-gray-500">{{ ctrans("Photos of the rejects (optional)") }}</span>
				<div v-if="item.photos.length" class="flex flex-wrap gap-2 text-xs">
					<a v-for="photo in item.photos" :key="photo.id" :href="photo.url" target="_blank" rel="noopener" class="secondaryLink inline-flex items-center gap-1">
						<FontAwesomeIcon :icon="faImage" fixed-width aria-hidden="true" />
						{{ photo.name }}
					</a>
				</div>
				<FileUpload ref="photoUploader" mode="basic" multiple accept="image/*" :auto="false" customUpload :chooseLabel="ctrans('Add photos')" :pt="chooseFilePt" @select="(event: { files: File[] }) => { form.photos = addPhotos(form.photos, event.files); photoUploader?.clear() }" />
			</div>
			<div v-for="(photo, index) in form.photos" :key="photo.name + index" class="flex items-center justify-between gap-2 rounded border border-gray-200 bg-gray-50 px-2 py-0.5 text-xs text-gray-700">
				<span class="truncate">{{ photo.name }}</span>
				<Button type="tertiary" size="xs" :aria-label="ctrans('Remove :name', { name: photo.name })" @click="removePhoto(index)">
					<template #icon>
						<FontAwesomeIcon :icon="faTrashAlt" class="text-red-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</div>
			<div v-for="(error, field) in form.errors" :key="field" class="text-xs text-red-600">{{ error }}</div>
		</div>
		<template #footer>
			<Button type="tertiary" :label="ctrans('Cancel')" @click="emit('close')">
				<template #icon>
					<FontAwesomeIcon :icon="faTimes" class="text-red-500" fixed-width aria-hidden="true" />
				</template>
			</Button>
			<Button type="secondary" :label="ctrans('Save check')" :loading="form.processing" @click="submit">
				<template #icon>
					<FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" />
				</template>
			</Button>
		</template>
	</Dialog>
</template>
