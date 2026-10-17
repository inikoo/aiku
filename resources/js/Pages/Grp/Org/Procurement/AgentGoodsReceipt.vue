<!--
    * Author: Vika Aqordi
    * Created on: 2026-10-16 16:10
    * Github: https://github.com/aqordeon
    * Copyright: 2026
-->

<script setup lang="ts">
import { ref } from "vue"
import { Head, router } from "@inertiajs/vue3"
import Dialog from "primevue/dialog"
import FileUpload from "primevue/fileupload"
import { notify } from "@kyvg/vue3-notification"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faClipboardCheck, faBan, faTimes, faTrashAlt, faFilePdf, faDownload, faExclamationTriangle, faCamera } from "@fal"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import TableAgentGoodsReceiptItems from "@/Components/Tables/Grp/Org/Procurement/TableAgentGoodsReceiptItems.vue"
import AgentGoodsReceiptItemCheckDialog, { type CheckableItem } from "@/Components/Procurement/AgentGoodsReceiptItemCheckDialog.vue"
import { addPhotos } from "@/Composables/usePhotoSelection"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { useLocaleStore } from "@/Stores/locale"
import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"

library.add(faClipboardCheck)

type Photo = { id: number; caption: string | null; file_name: string; is_image: boolean; url: string; download_url: string; delete_route: routeType | null }

type Props = {
	pageHead: PageHeadingTypes
	title: string
	receipt: {
		reference: string
		state: "received" | "cancelled"
		state_label: string
		received_at: string
		received_by: string | null
		supplier_code: string | null
		supplier_name: string | null
		supplier_delivery_note: string | null
		notes: string | null
		number_items: number
		quantity: number
		cancelled_at: string | null
		cancelled_by: string | null
	}
	items: object
	photos: Photo[]
	upload_route: routeType | null
	cancel_route: routeType | null
	receipt_note_route: routeType
}

const props = defineProps<Props>()

const checkingItem = ref<CheckableItem | null>(null)

const locale = useLocaleStore()

const formatQuantity = (value: number) => locale.number(Math.round(Number(value) * 1000) / 1000)

const chooseFilePt = { pcChooseButton: { root: { class: "!border !border-gray-300 !bg-white !text-gray-700 hover:!bg-gray-50" } } }

const photoUploader = ref<InstanceType<typeof FileUpload> | null>(null)
const isUploading = ref(false)

const uploadPhotos = (files: File[]) => {
	if (!props.upload_route || files.length === 0) {
		return
	}

	router.post(route(props.upload_route.name, props.upload_route.parameters), { attachments: files }, {
		forceFormData: true,
		preserveScroll: true,
		onStart: () => { isUploading.value = true },
		onFinish: () => {
			isUploading.value = false
			photoUploader.value?.clear()
		},
		onError: (errors: Record<string, string>) => notify({ title: ctrans("Upload failed"), text: Object.values(errors)[0] ?? ctrans("Something went wrong"), type: "error" }),
	})
}

const photoToDelete = ref<Photo | null>(null)
const isDeleting = ref(false)

const deletePhoto = () => {
	if (!photoToDelete.value?.delete_route) {
		return
	}

	router.delete(route(photoToDelete.value.delete_route.name, photoToDelete.value.delete_route.parameters), {
		preserveScroll: true,
		onStart: () => { isDeleting.value = true },
		onSuccess: () => { photoToDelete.value = null },
		onFinish: () => { isDeleting.value = false },
	})
}

const isCancelOpen = ref(false)
const isCancelling = ref(false)

const cancelReceipt = () => {
	if (!props.cancel_route) {
		return
	}

	router.post(route(props.cancel_route.name, props.cancel_route.parameters), {}, {
		preserveScroll: true,
		onStart: () => { isCancelling.value = true },
		onSuccess: () => { isCancelOpen.value = false },
		onFinish: () => { isCancelling.value = false },
		onError: (errors: Record<string, string>) => notify({ title: ctrans("Not cancelled"), text: Object.values(errors)[0] ?? ctrans("Something went wrong"), type: "error" }),
	})
}
</script>

<template>
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead">
		<template #other>
			<a :href="route(receipt_note_route.name, receipt_note_route.parameters)" target="_blank" rel="noopener">
				<Button type="tertiary" :label="ctrans('Receipt note')" :tooltip="ctrans('PDF for the supplier to sign')">
					<template #icon>
						<FontAwesomeIcon :icon="faFilePdf" class="text-red-500" fixed-width aria-hidden="true" />
					</template>
				</Button>
			</a>
			<Button v-if="cancel_route" type="tertiary" :label="ctrans('Cancel goods receipt')" @click="isCancelOpen = true">
				<template #icon>
					<FontAwesomeIcon :icon="faBan" class="text-red-500" fixed-width aria-hidden="true" />
				</template>
			</Button>
		</template>
	</PageHeading>

	<div class="grid gap-4 p-4 lg:grid-cols-[1fr_22rem]">
		<div class="flex min-w-0 flex-col gap-4">
			<div v-if="receipt.state === 'cancelled'" class="flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
				<FontAwesomeIcon :icon="faBan" fixed-width aria-hidden="true" />
				{{ ctrans("Cancelled :date by :name. Its lines went back in production.", { date: receipt.cancelled_at ? useFormatTime(receipt.cancelled_at) : "", name: receipt.cancelled_by ?? ctrans("someone") }) }}
			</div>

			<dl class="grid grid-cols-2 gap-x-6 gap-y-3 rounded-lg border border-gray-200 p-4 text-sm sm:grid-cols-3">
				<div>
					<dt class="text-xs text-gray-500">{{ ctrans("Supplier") }}</dt>
					<dd class="font-medium text-gray-800">
						<span class="whitespace-nowrap">{{ receipt.supplier_code }}</span>
						<span class="block text-xs font-normal text-gray-500">{{ receipt.supplier_name }}</span>
					</dd>
				</div>
				<div>
					<dt class="text-xs text-gray-500">{{ ctrans("Received on") }}</dt>
					<dd class="font-medium text-gray-800">{{ useFormatTime(receipt.received_at) }}</dd>
				</div>
				<div>
					<dt class="text-xs text-gray-500">{{ ctrans("Received by") }}</dt>
					<dd class="text-gray-800">{{ receipt.received_by ?? "-" }}</dd>
				</div>
				<div>
					<dt class="text-xs text-gray-500">{{ ctrans("Supplier's delivery note") }}</dt>
					<dd class="whitespace-nowrap text-gray-800">{{ receipt.supplier_delivery_note ?? "-" }}</dd>
				</div>
				<div>
					<dt class="text-xs text-gray-500">{{ ctrans("Lines") }}</dt>
					<dd class="tabular-nums text-gray-800">{{ locale.number(receipt.number_items) }}</dd>
				</div>
				<div>
					<dt class="text-xs text-gray-500">{{ ctrans("Units") }}</dt>
					<dd class="tabular-nums text-gray-800">{{ formatQuantity(receipt.quantity) }}</dd>
				</div>
				<div v-if="receipt.notes" class="col-span-2 sm:col-span-3">
					<dt class="text-xs text-gray-500">{{ ctrans("Note") }}</dt>
					<dd class="whitespace-pre-line text-gray-800">{{ receipt.notes }}</dd>
				</div>
			</dl>

			<TableAgentGoodsReceiptItems :data="items" tab="items" @check="(item) => checkingItem = { ...item }" />
		</div>

		<div class="flex flex-col gap-3 rounded-lg border border-gray-200 p-4">
			<div class="flex items-center justify-between gap-2">
				<h3 class="text-sm font-semibold text-gray-700">{{ ctrans("Signed delivery note") }}</h3>
				<FileUpload
					v-if="upload_route"
					ref="photoUploader"
					mode="basic"
					:auto="false"
					customUpload
					multiple
					accept="image/*,.pdf"
					:disabled="isUploading"
					:chooseLabel="ctrans('Add photo')"
					:pt="chooseFilePt"
					@select="(event: { files: File[] }) => uploadPhotos(addPhotos([], event.files))"
				/>
			</div>

			<div v-if="photos.length === 0" class="flex items-center gap-2 rounded-md border border-amber-300 bg-amber-50 p-3 text-xs text-amber-800">
				<FontAwesomeIcon :icon="faExclamationTriangle" fixed-width aria-hidden="true" />
				{{ ctrans("Not uploaded yet. Take a photo of the delivery note the supplier signed and add it here.") }}
			</div>

			<div v-else class="grid grid-cols-2 gap-2">
				<div v-for="photo in photos" :key="photo.id" class="group relative overflow-hidden rounded-md border border-gray-200">
					<a :href="photo.url" target="_blank" rel="noopener" class="block aspect-[3/4] bg-gray-50">
						<img v-if="photo.is_image" :src="photo.url" :alt="photo.caption ?? photo.file_name" class="h-full w-full object-cover" loading="lazy" />
						<span v-else class="flex h-full flex-col items-center justify-center gap-1 text-gray-500">
							<FontAwesomeIcon :icon="faFilePdf" size="2x" fixed-width aria-hidden="true" />
							<span class="px-2 text-center text-xs">{{ photo.file_name }}</span>
						</span>
					</a>
					<div class="flex items-center justify-between gap-1 border-t border-gray-200 bg-white px-1.5 py-1">
						<a :href="photo.download_url" class="text-gray-500 hover:text-gray-800" v-tooltip="ctrans('Download')">
							<FontAwesomeIcon :icon="faDownload" fixed-width aria-hidden="true" />
						</a>
						<Button v-if="photo.delete_route" type="tertiary" size="xs" v-tooltip="ctrans('Remove')" :aria-label="ctrans('Remove :name', { name: photo.file_name })" @click="photoToDelete = photo">
							<template #icon>
								<FontAwesomeIcon :icon="faTrashAlt" class="text-red-500" fixed-width aria-hidden="true" />
							</template>
						</Button>
					</div>
				</div>
			</div>

			<p v-if="upload_route" class="flex items-center gap-1 text-xs text-gray-500">
				<FontAwesomeIcon :icon="faCamera" fixed-width aria-hidden="true" />
				{{ ctrans("Photos or PDF, up to 20 MB each") }}
			</p>
		</div>
	</div>

	<AgentGoodsReceiptItemCheckDialog :item="checkingItem" @close="checkingItem = null" />

	<Dialog v-model:visible="isCancelOpen" modal :header="ctrans('Cancel goods receipt :reference', { reference: receipt.reference })" :style="{ width: '28rem', maxWidth: 'calc(100vw - 2rem)' }" :draggable="false">
		<p class="text-sm text-gray-700">{{ ctrans(":lines lines (:units units) go back to In production and leave the warehouse stock. Do this only when the goods were received by mistake.", { lines: receipt.number_items, units: formatQuantity(receipt.quantity) }) }}</p>
		<template #footer>
			<Button type="tertiary" :label="ctrans('Keep it')" @click="isCancelOpen = false">
				<template #icon>
					<FontAwesomeIcon :icon="faTimes" class="text-gray-500" fixed-width aria-hidden="true" />
				</template>
			</Button>
			<Button type="tertiary" :label="ctrans('Cancel goods receipt')" :loading="isCancelling" @click="cancelReceipt">
				<template #icon>
					<FontAwesomeIcon :icon="faExclamationTriangle" class="text-amber-500" fixed-width aria-hidden="true" />
				</template>
			</Button>
		</template>
	</Dialog>

	<Dialog :visible="photoToDelete !== null" modal :header="ctrans('Remove photo')" :style="{ width: '28rem', maxWidth: 'calc(100vw - 2rem)' }" :draggable="false" @update:visible="(value) => { if (!value) photoToDelete = null }">
		<p class="text-sm text-gray-700">{{ ctrans("Remove :name from this goods receipt?", { name: photoToDelete?.file_name ?? "" }) }}</p>
		<template #footer>
			<Button type="tertiary" :label="ctrans('Keep it')" @click="photoToDelete = null">
				<template #icon>
					<FontAwesomeIcon :icon="faTimes" class="text-gray-500" fixed-width aria-hidden="true" />
				</template>
			</Button>
			<Button type="tertiary" :label="ctrans('Remove')" :loading="isDeleting" @click="deletePhoto">
				<template #icon>
					<FontAwesomeIcon :icon="faTrashAlt" class="text-red-500" fixed-width aria-hidden="true" />
				</template>
			</Button>
		</template>
	</Dialog>
</template>
