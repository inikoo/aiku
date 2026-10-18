<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue"
import { ctrans } from "@/Composables/useTrans"

import Modal from "@/Components/Utils/Modal.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faFile as falFile, faTimes, faPlus } from "@fal"
import { faFileDownload, faDownload, faTimesCircle, faCheckCircle } from "@fas"
import { faInfoCircle } from "@fad"
import { library } from "@fortawesome/fontawesome-svg-core"
import axios from "axios"
import { Upload } from "@/types/Upload"

import { useFormatTime } from "@/Composables/useFormatTime"
import { UploadPallet } from "@/types/Pallet"
import { Link, router } from "@inertiajs/vue3"
import { useEchoGrpPersonal } from "@/Stores/echo-grp-personal"
import Papa from "papaparse"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { notify } from "@kyvg/vue3-notification"
import Select from "primevue/select"
import InputText from "primevue/inputtext"

library.add(
	falFile,
	faTimes,
	faPlus,
	faTimesCircle,
	faCheckCircle,
	faFileDownload,
	faDownload,
	faInfoCircle
)

const props = defineProps<{
	scope?: string
	title?: {
		label?: string
		information?: string
	}
	additionalDataToSend?: string[]
	attachmentRoutes?: object
	options?: {
		name: string
		code: string
	}[]
	withCaption?: boolean
	multiple?: boolean
}>()

const model = defineModel()

const typeEmployee = props.options?.length ? props.options :  [
	{ name: ctrans("Other"), code: "Other" },
	{ name: ctrans("CV"), code: "CV" },
	{ name: ctrans("Contract"), code: "Contract" },
]

// const emits = defineEmits();

// const { isUploaded } = toRefs(props)

const isLoadingUpload = ref(false)
const dataHistoryFileUpload: any = ref([])
const isLoadingHistory = ref(false)
const isDraggedFile = ref(false)
const errorMessage = ref<string | null>(null)
const selectedType = ref()
const selectedFiles = ref<File[]>([])
const caption = ref("")
const isCaptionRequired = computed(() => props.withCaption && selectedType.value?.code === "Other")
const isCaptionMissing = computed(() => isCaptionRequired.value && !caption.value.trim())

const onUploadFiles = (filesUploaded?: FileList | null) => {
	const files = Array.from(filesUploaded ?? [])
	errorMessage.value = null

	if (!files.length) {
		return
	}

	if (files.some((file) => !file.name?.includes("."))) {
		errorMessage.value = ctrans("Every file needs an extension")
		return
	}

	selectedFiles.value = props.multiple ? [...selectedFiles.value, ...files] : [files[0]]
}

const formatFileSize = (bytes: number): string => {
	const units = ["B", "KB", "MB", "GB"]
	let size = bytes
	let unitIndex = 0

	while (size >= 1024 && unitIndex < units.length - 1) {
		size /= 1024
		unitIndex++
	}

	return `${size.toFixed(unitIndex === 0 ? 0 : 1)} ${units[unitIndex]}`
}

const fileExtension = (file: File): string => file.name.split(".").pop()?.toLowerCase() ?? ""

const removeFile = (index: number) => {
	selectedFiles.value.splice(index, 1)
}

// Method: submit the selected file to server
const submitUpload = async () => {
	if (!selectedType.value) {
		notify({
			title: "Type not selected",
			text: "Please select a type before uploading.",
			type: "error",
		})
		return
	}

	if (!props.attachmentRoutes?.attachRoute?.name) {
		notify({
			title: "Something went wrong.",
			text: "Route is not set yet.",
			type: "error",
		})
		return
	}

	isDraggedFile.value = false
	errorMessage.value = null
	isLoadingUpload.value = true
	try {
		await axios.post(
			route(
				props.attachmentRoutes?.attachRoute?.name,
				props.attachmentRoutes?.attachRoute?.parameters
			),
			{
				attachments: selectedFiles.value,
				scope: selectedType.value.code,
				...(props.withCaption && caption.value.trim() ? { caption: caption.value.trim() } : {}),
			},
			{
				headers: { "Content-Type": "multipart/form-data" },
			}
		)
		notify({
			title: "Success!",
			text: "The upload has successfully",
			type: "success",
		})
		router.reload({
			// onSuccess: () => {
			// 	console.log("reload")
			// },
			// onError: () => {
			// 	console.log("errorrrrrrrrr")
			// }
		})  // To reload the table to show new data
		closeModal()
		// useEchoGrpPersonal().isShowProgress = true
	} catch (error: any) {
		console.error(error)
		errorMessage.value = error?.response?.data?.message
	}
	isLoadingUpload.value = false
}

// Method: refresh all like new open the modal
const clearAll = () => {
	selectedFiles.value = []
	caption.value = ""
	errorMessage.value = null
}

const closeModal = () => {
	/*    useEchoGrpPersonal().isShowProgress = false */
	useEchoGrpPersonal().isShowProgress = false
	model.value = false
	selectedFiles.value = []
	caption.value = ""
}

onMounted(() => {
	if (typeEmployee.length === 1) {
		selectedType.value = typeEmployee[0]
	}
})
</script>

<template>
	<Modal :isOpen="model" @onClose="() => closeModal()" :closeButton="true" width="w-[500px]">
		<div class="flex flex-col justify-between pb-4 px-3">
			<div>
				<!-- Title -->
				<div class="flex justify-center py-2 text-gray-600 font-medium mb-3">
					<div>
						<div class="flex gap-x-0.5">
							{{ title?.label }}
						</div>
					</div>
				</div>

				<!-- Section: Upload box -->
				<div class="grid gap-x-3 px-1">
					<div class="mb-2 w-full flex justify-end">
						<Select
							v-model="selectedType"
							:options="typeEmployee"
							optionLabel="name"
							fluid
							:filter="typeEmployee.length > 5"
							filterMatchMode="contains"
							autoFilterFocus
							resetFilterOnHide
							:filterPlaceholder="ctrans('Search type')"
							:emptyFilterMessage="ctrans('No type found')"
							:placeholder="ctrans('Select a type')"
							class="w-full md:w-56"
							:dropdownIcon="typeEmployee.length === 1 ? 's' : 'pi pi-chevron-down'"
							xdisabled>
							<template #optiongroup="slotProps">
								<div class="flex items-center">
									<div>{{ slotProps.option.label }}</div>
								</div>
							</template>
						</Select>
					</div>
					<div v-if="withCaption" class="mb-2">
						<InputText
							v-model="caption"
							fluid
							maxlength="255"
							:placeholder="isCaptionRequired ? ctrans('What is this document? (required)') : ctrans('Description (optional)')"
							:invalid="isCaptionMissing && selectedFiles.length > 0" />
						<p v-if="multiple && selectedFiles.length > 1" class="mt-1 text-xs text-gray-500">
							{{ ctrans("Every file gets this label; leave it empty to use each file name. You can change each label later.") }}
						</p>
					</div>
					<div
						@drop="(e: any) => (e.preventDefault(), onUploadFiles(e.dataTransfer.files))"
						@dragover.prevent
						@dragenter.prevent
						@dragleave.prevent
						class="overflow-hidden relative w-full max-w-full flex items-center justify-center rounded-lg border border-dashed border-gray-700/25 px-6 py-3 bg-gray-400/10"
						:class="[
							{ 'hover:bg-gray-400/20': !isLoadingUpload },
							errorMessage ? 'errorShake' : '',
						]">
						<!-- Section: Upload area -->
						<div
							v-if="selectedFiles.length"
							class="text-gray-500 flex flex-col items-center gap-y-2 w-full">
							<div
								v-for="(file, index) in selectedFiles"
								:key="`${file.name}-${index}`"
								class="max-w-lg w-full flex items-center justify-center gap-x-2 mx-auto overflow-hidden">
								<FontAwesomeIcon
									icon="fal fa-file"
									class="h-5 w-5 text-gray-300"
									fixed-width aria-hidden="true" />
								<span class="truncate">{{ file.name }}</span>
								<span class="shrink-0 rounded border border-gray-300 bg-white px-1 font-mono text-xxs uppercase text-gray-500">.{{ fileExtension(file) }}</span>
								<span class="shrink-0 text-xs tabular-nums text-gray-400">{{ formatFileSize(file.size) }}</span>
								<button
									v-if="multiple && selectedFiles.length > 1"
									type="button"
									class="text-gray-400 hover:text-red-500"
									v-tooltip="ctrans('Remove file')"
									@click="() => removeFile(index)">
									<FontAwesomeIcon icon="fal fa-times" fixed-width aria-hidden="true" />
								</button>
							</div>
							<div class="flex gap-x-2">
								<label v-if="multiple" class="relative cursor-pointer">
									<input
										type="file"
										multiple
										class="sr-only"
										@change="(e: any) => (onUploadFiles(e.target.files), (e.target.value = ''))" />
									<span class="inline-flex items-center gap-x-1 rounded border border-gray-300 bg-white px-2 py-1 text-xs text-gray-600 hover:bg-gray-50">
										<FontAwesomeIcon icon="fal fa-plus" class="text-green-600" fixed-width aria-hidden="true" />
										{{ ctrans("Add more files") }}
									</span>
								</label>
								<Button
									@click="() => clearAll()"
									:label="multiple && selectedFiles.length > 1 ? ctrans('Remove all files') : ctrans('Remove file')"
									type="negative"
									size="s" />
							</div>
						</div>

						<!-- Section: Upload (empty state) -->
						<div v-else-if="!isLoadingUpload" class="">
							<label
								for="fileInput"
								class="absolute cursor-pointer rounded-md inset-0 focus-within:outline-none focus-within:ring-0 focus-within:ring-gray-400 focus-within:ring-offset-0">
								<input
									type="file"
									name="file"
									id="fileInput"
									class="sr-only"
									:multiple="multiple"
									@change="(e: any) => (onUploadFiles(e.target.files), (e.target.value = ''))"
									ref="fileInput" />
								<div
									v-if="isDraggedFile"
									class="text-2xl text-gray-500 h-full flex justify-center items-center">
									{{ ctrans("Drop your file here") }}
								</div>
							</label>

							<div v-if="!isDraggedFile" class="text-center text-gray-500">
								<div class="flex justify-center text-sm font-medium leading-6">
									{{ ctrans("Upload file") }}
								</div>
								<div class="flex w-fit mx-auto text-xs leading-6">
									<p class="">
										{{ ctrans("Drag and drop, or browse your files") }}
									</p>
								</div>
							</div>
						</div>
					</div>

					<div v-if="errorMessage" class="mt-1 text-red-500 text-xs italic">
						*{{ errorMessage }}
					</div>

					<!-- Section: Attachment preview -->
					<!-- <Transition name="headlessui"> -->
						<!-- <div v-if="selectedFile" class="text-xxs mt-3 max-w-3xl overflow-x-hidden"> -->
							<div class="flex justify-end mt-4">
								<Button
									@click="() => submitUpload()"
									label="Submit"
									size="l"
									full
									:disabled="!selectedFiles.length || !selectedType || isCaptionMissing"
									:loading="isLoadingUpload" />
							</div>
						<!-- </div> -->
						<!-- <div v-else /> -->
					<!-- </Transition> -->
				</div>
			</div>
		</div>
	</Modal>
</template>

<style scoped>
/* Adjust the selector based on the rendered DOM of PrimeVue's Select.
   This example assumes the arrow is contained in an element with the class `.p-dropdown-trigger` */
.no-arrow .p-dropdown-trigger {
  display: none;
}
</style>