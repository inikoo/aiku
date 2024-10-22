<script setup lang="ts">
import { faCube, faStar, faImage } from "@fas"
import { faPencil } from "@far"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import Gallery from "@/Components/Fulfilment/Website/Gallery/Gallery.vue"
import Image from "@/Components/Image.vue"
import { ref, defineProps, defineEmits } from "vue"
import Button from "@/Components/Elements/Buttons/Button.vue"

// Define the structure of each image in the array
interface ImageObject {
	source: string | null // You can specify the type of the source (e.g., URL as a string)
	link_data?: any // Keep other data intact (like link_data)
}

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
const activeImageIndex = ref<number | null>(null) // Track which image is being edited

const setImage = (imageData: any) => {
    if (activeImageIndex.value !== null) {
        // Ensure the proper data structure with fieldValue.value.images
        const images = props.web_block.layout.data.fieldValue.value?.images || [];

        // Ensure the array is large enough to accommodate the index
        while (images.length <= activeImageIndex.value) {
            images.push({
                source: null,
                link_data: null,
            });
        }

        // Flatten the image data if it contains nested source
        const flattenedSource = imageData.data[0].source ? imageData.data[0].source : imageData.data[0];

        // Update the image source with the flattened data
        images[activeImageIndex.value].source = {
            ...flattenedSource,
        };

        // Emit the updated model value, ensuring that the fieldValue contains only the "value" key
        emits("update:modelValue", {
            ...props.web_block.layout.data.fieldValue,
            value: { images },
        });

        emits("autoSave");
    } else {
        console.error("Invalid index or modelValue structure.");
    }

    // Reset activeImageIndex and close the gallery
    openGallery.value = false;
    activeImageIndex.value = null;
};

const onUpload = (uploadData: any) => {
    if (activeImageIndex.value !== null && uploadData.data && uploadData.data.length <= 1) {
        // Ensure the proper data structure with fieldValue.value.images
        const images = props.web_block.layout.data.fieldValue.value?.images || [];

        // Ensure the array is large enough
        while (images.length <= activeImageIndex.value) {
            images.push({
                source: null,
                link_data: null,
            });
        }

        // Flatten the uploaded data if it contains nested source
        const flattenedSource = uploadData.data[0].source ? uploadData.data[0].source : uploadData.data[0];

        // Update the image source with the flattened data
        images[activeImageIndex.value].source = {
            ...flattenedSource,
        };
        // Emit the updated model value, ensuring fieldValue contains only the "value" key
        emits("update:modelValue", {
            ...props.web_block.layout.data.fieldValue,
            value: { images },
        });

        emits("autoSave");
    } else {
        console.error("Invalid index, no files, or multiple files detected.");
    }

    // Reset activeImageIndex and close the gallery
    openGallery.value = false;
    activeImageIndex.value = null;
};

const openImageGallery = (index: number) => {
	activeImageIndex.value = index // Track which slot is being edited
	openGallery.value = true // Open the gallery to allow image selection
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

// Create a function to generate the required number of image slots based on layout_type
const getImageSlots = (layoutType: string) => {
	// Adjust the number of slots based on layout type
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
	<div v-if="web_block?.layout?.data?.fieldValue?.value?.images" class="flex flex-wrap">
		<div
			v-for="index in getImageSlots(web_block?.layout?.data?.fieldValue?.value?.layout_type)"
			:key="index"
			class="p-2"
			:class="
				getColumnWidthClass(
					web_block?.layout?.data?.fieldValue?.value?.layout_type,
					index - 1
				)
			">
			<!-- If image exists at this slot -->
			<div
				v-if="web_block?.layout?.data?.fieldValue?.value?.images?.[index - 1]?.source"
				class="transition-shadow aspect-h-1 aspect-w-1 w-full bg-gray-200">
				<div v-if="isEditable" class="absolute top-2 right-2 flex space-x-2">
					<Button
						:icon="['far', 'fa-pencil']"
						size="xs"
						@click="openImageGallery(index - 1)" />
				</div>
				<Image
					:src="web_block?.layout?.data?.fieldValue?.value?.images?.[index - 1]?.source"
					class="w-full object-cover object-center group-hover:opacity-75" />
			</div>

			<!-- If no image, show placeholder for image upload -->
			<div v-else-if="isEditable" class="p-5">
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
