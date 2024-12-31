<script setup lang="ts">
import { faCube, faStar, faImage } from "@fas"
import { faPencil } from "@far"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import Gallery from "@/Components/Fulfilment/Website/Gallery/Gallery.vue"
import Image from "@/Components/Image.vue"
import { ref, toRaw } from "vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import GalleryManagement from "@/Components/Utils/GalleryManagement/GalleryManagement.vue"
import Modal from "@/Components/Utils/Modal.vue"
import { notify } from "@kyvg/vue3-notification"
import axios from "axios"
import { trans } from "laravel-vue-i18n";

library.add(faCube, faStar, faImage, faPencil)

const props = defineProps<{
	modelValue: any
	webpageData?: any
	blockData:Object
}>()
const emits = defineEmits<{
	(e: "update:modelValue", value: any): void
	(e: "autoSave"): void
}>()

const openGallery = ref(false)
const activeImageIndex = ref<number | null>(null)

const setImage = (imageData: any) => {
	if (activeImageIndex.value !== null) {
		const images = props.modelValue?.value?.images || []

		while (images.length <= activeImageIndex.value) {
			images.push({
				source: null,
				link_data: null,
			})
		}
	
		const flattenedSource = imageData[0].source ? imageData[0].source : imageData

		images[activeImageIndex.value].source = {
			...flattenedSource,
		}
    

		emits("update:modelValue", {
		...toRaw(props.modelValue), // Strips Vue's reactivity for safe usage
		value: { ...toRaw(props.modelValue?.value), images: images },
		});


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
		setImage(response.data.data)
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
	<div v-if="modelValue?.value?.images" class="flex flex-wrap">
		<div
			v-for="index in getImageSlots(modelValue?.value?.layout_type)"
			:key="index"
			class="group relative p-2 "
			:class="getColumnWidthClass(modelValue?.value?.layout_type, index - 1)">
			<a
				v-if="modelValue?.value?.images?.[index - 1]?.source"
				:href="getHref(index - 1)"
				target="_blank"
				rel="noopener noreferrer"
				class="transition-shadow aspect-h-1 aspect-w-1 w-full">
				<Image
					:src="modelValue?.value?.images?.[index - 1]?.source"
					class="w-full object-cover object-center group-hover:opacity-75"
				/>

			</a>

			<div
				class="absolute flex items-center justify-center gap-x-2 top-0 py-1 opacity-0 group-hover:-top-4 group-hover:opacity-100 transition-all left-0 w-full hover:text-indigo-500 rounded-lg cursor-pointer"
				@click="openImageGallery(index - 1)">
				<FontAwesomeIcon icon='fas fa-image' class='text-lg opacity-40' fixed-width aria-hidden='true' />
				<div class="text-sm font-semibold">
					{{ trans("Change image") }}
				</div>
			</div>
		</div>
	</div>

	<!-- <Gallery
		:open="openGallery"
		@on-close="openGallery = false"
		:uploadRoutes="route(webpageData?.images_upload_route.name, { modelHasWebBlocks: id })"
		@onPick="setImage"
		@onUpload="onUpload" /> -->


		<Modal :isOpen="openGallery" @onClose="() => (openGallery = false)" width="w-3/4">
			<GalleryManagement 
				:maxSelected="1" 
				:closePopup="() => (openGallery = false)" 
				@submitSelectedImages="setImage" 
				:submitUpload="onUpload"
				:uploadRoute="{
					...webpageData?.images_upload_route,
					parameters: {
						modelHasWebBlocks: blockData.id
					},
				}"
			/>
		</Modal>
</template>
