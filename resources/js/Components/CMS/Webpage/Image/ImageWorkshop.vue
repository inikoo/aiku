<script setup lang="ts">
import { faCube, faStar, faImage } from "@fas"
import { faPencil } from "@far"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import Gallery from "@/Components/Fulfilment/Website/Gallery/Gallery.vue"
import Image from "@/Components/Image.vue"
import { ref, defineProps, defineEmits, toRaw, inject } from "vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import GalleryManagement from "@/Components/Utils/GalleryManagement/GalleryManagement.vue"
import Modal from "@/Components/Utils/Modal.vue"
import { notify } from "@kyvg/vue3-notification"
import axios from "axios"
import { trans } from "laravel-vue-i18n";
import { set } from "lodash";
import { routeType } from "@/types/route"

library.add(faCube, faStar, faImage, faPencil)

const props = defineProps<{
	modelValue: any
	webpageData?: any
	blockData:Object
	// uploadRoutes: routeType
}>()

const emits = defineEmits<{
	(e: "update:modelValue", value: any): void
	(e: "autoSave"): void
}>()

const isInWorkshop = inject('isInWorkshop', false)

const openGallery = ref(false)
const activeImageIndex = ref<number | null>(null)

// Method: on select image from stock images/uploaded images
const submitImage = (imageData: {source: {}}[]) => {
	if (activeImageIndex.value !== null) {
		// const images = toRaw(props.modelValue?.value?.images) || []

		// props.modelValue?.value?.images.splice(2, 0, imageData[0])

		// const fff = ['cccc', 'xxxx', 'ffff'];
		// const dataToPut = 'bebebe';

		// Ensure the array is long enough by filling with empty objects if necessary
		while (props.modelValue?.value?.images.length <= activeImageIndex.value) {
			props.modelValue?.value?.images.push({});
		}

		// Replace the value at the specified index with dataToPut
		// props.modelValue.value.images[activeImageIndex.value] = imageData[0];
		set(props.modelValue.value, ['images', activeImageIndex.value], {
			link_data: {},
			source: toRaw(imageData[0] || {})?.source,
		})

		// console.log(props.modelValue?.value?.images)


		emits("autoSave")
	} else {
		console.error("Invalid index or modelValue structure.")
	}

	openGallery.value = false
	activeImageIndex.value = null
}

const onUpload = async (files: File[], clear: Function) => {
	try {
		const formData = new FormData()
		Array.from(files).forEach((file, index) => {
			formData.append(`images[${index}]`, file)
		})
		const response = await axios.post(
			route(props.webpageData?.images_upload_route.name, { modelHasWebBlocks: props.blockData.id }),
			formData,
			{
				headers: {
					"Content-Type": "multipart/form-data",
				},
			}
		)
		submitImage(response.data.data)
	} catch (error) {
		console.log(error)
		notify({
			title: "Failed",
			text: "Error while uploading data",
			type: "error",
		})
	}
}

/* const onUpload = (uploadData: any, clear : Function) => {
	if (activeImageIndex.value !== null && uploadData && uploadData.length <= 1) {
		const images = props.modelValue?.value?.images || []
		while (images.length <= activeImageIndex.value) {
			images.push({
				source: null,
				link_data: null,
			})
		}

		const flattenedSource = uploadData[0].source
			? uploadData[0].source
			: uploadData[0]

		images[activeImageIndex.value].source = {
			...flattenedSource,
		}

		emits("update:modelValue", {
			...props.modelValue,
			value: {
                ...props.modelValue.value,
                images: images,
            },
		})

		emits("autoSave")
	} else {
		console.error("Invalid index, no files, or multiple files detected.")
	}

	openGallery.value = false
	activeImageIndex.value = null
}
 */
const openImageGallery = (index: number) => {
	activeImageIndex.value = index
	openGallery.value = true
}

const getHref = (index: number) => {
	const image = props.modelValue?.value?.images?.[index]

	if (image?.link_data?.url) {
		return image.link_data.url
	}

	return image?.link_data?.workshop_url
}

const getColumnWidthClass = (layoutType: string, index: number) => {
	switch (layoutType) {
		case "12":
			return index === 0 ? " sm:w-1/2 md:w-1/3" : " sm:w-1/2 md:w-2/3"
		case "21":
			return index === 0 ? " sm:w-1/2 md:w-2/3" : " sm:w-1/2 md:w-1/3"
		case "13":
			return index === 0 ? " md:w-1/4" : " md:w-3/4"
		case "31":
			return index === 0 ? " sm:w-1/2 md:w-3/4" : " sm:w-1/2 md:w-1/4"
		case "211":
			return index === 0 ? " md:w-1/2" : " md:w-1/4"
		case "2":
			return index === 0 ? " md:w-1/2" : " md:w-1/2"
		case "3":
			return index === 0 ? " md:w-1/3" : " md:w-1/3"
		case "4":
			return index === 0 ? " md:w-1/4" : " md:w-1/4"
		default:
			return "w-full"
	}
}

const getImageSlots = (layoutType: string) => {
	switch (layoutType) {
		case "4":
			return 4
		case "3":
		case "211":
			return 3
		case "2":
		case "12":
		case "21":
		case "13":
		case "31":
			return 2
		default:
			return 1
	}
}
</script>

<template>
	<div class="flex flex-wrap">
		<div
			v-for="index in getImageSlots(modelValue?.value?.layout_type)"
			:key="index + modelValue?.value?.images?.[index - 1]?.source?.avif"
			class="group relative p-2 hover:bg-white/40"
			:class="getColumnWidthClass(modelValue?.value?.layout_type, index - 1)">
			<a
				:href="getHref(index - 1)"
				target="_blank"
				rel="noopener noreferrer"
				class="w-full"
				@click="(e) => isInWorkshop ? e.preventDefault() : null">
				<Image
					:src="modelValue?.value?.images?.[index - 1]?.source"
				/>
			</a>

			<div
				class="bg-gray-800/50 hover:bg-gray-800/80 w-fit absolute flex items-center justify-center gap-x-2 py-2 px-3 opacity-0 top-2/3 group-hover:top-1/2 group-hover:-translate-y-1/2 group-hover:opacity-100 transition-all left-1/2 -translate-x-1/2 text-gray-300 hover:text-white rounded cursor-pointer"
				@click="openImageGallery(index - 1)">
				<FontAwesomeIcon icon='fas fa-image' class='text-lg opacity-40' fixed-width aria-hidden='true' />
				<div class="text-sm font-semibold whitespace-nowrap">
					{{ trans("Change image") }}
				</div>
			</div>
		</div>

		<!-- <Gallery
		:open="openGallery"
		@on-close="openGallery = false"
		:uploadRoutes="route(webpageData?.images_upload_route.name, { modelHasWebBlocks: id })"
		@onPick="submitImage"
		@onUpload="onUpload" /> -->


		<Modal :isOpen="openGallery" @onClose="() => (openGallery = false, activeImageIndex = null)" width="w-3/4">
			<GalleryManagement 
				:maxSelected="1" 
				:closePopup="() => (openGallery = false)" 
				@submitSelectedImages="submitImage" 
				:submitUpload="onUpload"
				:uploadRoute="{
					...webpageData?.images_upload_route,
					parameters: {
						modelHasWebBlocks: blockData.id
					},
				}"
			/>
		</Modal>
	</div>

	
</template>
