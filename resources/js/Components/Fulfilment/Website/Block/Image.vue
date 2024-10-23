<script setup lang="ts">
import { faCube, faStar, faImage } from "@fas"
import { faPencil } from "@far"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import Gallery from "@/Components/Fulfilment/Website/Gallery/Gallery.vue"
import Image from "@/Components/Image.vue"
import { ref, defineProps, defineEmits } from "vue"
import Button from "@/Components/Elements/Buttons/Button.vue"

library.add(faCube, faStar, faImage, faPencil)

const props = defineProps<{
	modelValue: any
	webpageData?: any
	web_block?: Object
	id?: Number
	type?: String
	isEditable?: boolean
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

		const flattenedSource = imageData.data[0].source
			? imageData.data[0].source
			: imageData.data[0]

		images[activeImageIndex.value].source = {
			...flattenedSource,
		}

		emits("update:modelValue", {
			...props.modelValue,
			value: { images },
		})

		emits("autoSave")
	} else {
		console.error("Invalid index or modelValue structure.")
	}

	openGallery.value = false
	activeImageIndex.value = null
}

const onUpload = (uploadData: any) => {
	if (activeImageIndex.value !== null && uploadData.data && uploadData.data.length <= 1) {
		const images = props.modelValue?.value?.images || []
		while (images.length <= activeImageIndex.value) {
			images.push({
				source: null,
				link_data: null,
			})
		}

		const flattenedSource = uploadData.data[0].source
			? uploadData.data[0].source
			: uploadData.data[0]

		images[activeImageIndex.value].source = {
			...flattenedSource,
		}

		emits("update:modelValue", {
			...props.modelValue,
			value: { images },
		})

		emits("autoSave")
	} else {
		console.error("Invalid index, no files, or multiple files detected.")
	}

	openGallery.value = false
	activeImageIndex.value = null
}

const openImageGallery = (index: number) => {
	activeImageIndex.value = index
	openGallery.value = true
}

const getHref = (index: number, isEditable: any) => {
  const image = props.modelValue?.value?.images?.[index];
  
  console.log(image,'this url', index);
  if (isEditable) {
    return image?.link_data?.workshop_url;
  }
  
  if (image?.link_data?.url) {
    return image.link_data.url;
  }

  return null;
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
			class="p-2"
			:class="
				getColumnWidthClass(
					modelValue?.value?.layout_type,
					index - 1
				)
			">
			<a
				v-if="modelValue?.value?.images?.[index - 1]?.source"
				:href="getHref(index - 1, isEditable)"
                target="_blank"
                rel="noopener noreferrer" 
				class="transition-shadow aspect-h-1 aspect-w-1 w-full ">
				<div v-if="isEditable" class="absolute top-2 right-2 flex space-x-2">
					<Button
						:icon="['far', 'fa-pencil']"
						size="xs"
						@click="openImageGallery(index - 1)" />
				</div>
				<Image
					:src="modelValue?.value?.images?.[index - 1]?.source"
					class="w-full object-cover object-center group-hover:opacity-75" />
			</a>

			<div v-else-if="isEditable" class="py-3">
				<div
					type="button"
					@click="openImageGallery(index - 1)"
					class="relative block w-full rounded-lg border-2 border-dashed border-gray-300 p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
					<font-awesome-icon
						:icon="['fas', 'image']"
						class="mx-auto h-12 w-12 text-gray-400" />
					<span class="mt-2 block text-sm font-semibold text-gray-900"
						>Click Pick Image</span
					>
				</div>
			</div>
		</div>
	</div>

	<!-- Gallery for picking/uploading images -->
	<Gallery
		:open="openGallery"
		@on-close="openGallery = false"
		:uploadRoutes="route(webpageData?.images_upload_route.name, { modelHasWebBlocks: id })"
		@onPick="setImage"
		@onUpload="onUpload">
	</Gallery>
</template>
