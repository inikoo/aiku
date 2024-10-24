<script setup lang="ts">
import { ref } from "vue"
import { trans } from "laravel-vue-i18n"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Gallery from "@/Components/Fulfilment/Website/Gallery/Gallery.vue"
import Image from "@/Components/Image.vue"
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Slider from 'primevue/slider';
import { notify } from "@kyvg/vue3-notification"
import axios from "axios"
import PaddingMarginProperty from '@/Components/Websites/Fields/Properties/PaddingMarginProperty.vue'
import BorderProperty from '@/Components/Websites/Fields/Properties/BorderProperty.vue'

import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faImage, faPhotoVideo, faLink } from "@fal"

import { routeType } from "@/types/route"


library.add(faImage, faPhotoVideo, faLink)

const props = withDefaults(
	defineProps<{
		modelValue: any
		uploadRoutes: routeType
	}>(),
	{}
)

const emits = defineEmits<{
	(e: "update:modelValue", value: string | number): void
	(e: "onUpload", value: Files[]): void
}>()

const isOpenGalleryImages = ref(false)
const isDragging = ref(false)
const fileInput = ref(null)

const onUpload = async () => {
	try {
		const formData = new FormData()
		Array.from(addedFiles.value).forEach((file, index) => {
			formData.append(`images[${index}]`, file)
		})
		const response = await axios.post(
			route(props.uploadRoutes.name, props.uploadRoutes.parameters),
			formData,
			{
				headers: {
					"Content-Type": "multipart/form-data",
				},
			}
		)
		emits("update:modelValue", response.data.data[0])
	} catch (error) {
		console.error("error", error)
		notify({
			title: "Failed",
			text: "Error while Uploading data",
			type: "error",
		})
	}
}

const addComponent = (event) => {
	addedFiles.value = event.target.files
	onUpload()
}

const dragOver = (e) => {
	e.preventDefault()
	isDragging.value = true
}

const dragLeave = () => {
	isDragging.value = false
}

const drop = (e) => {
	e.preventDefault()
	addedFiles.value = e.dataTransfer.files
	isDragging.value = false
	onUpload()
}

const onPickImage = (e) => {
	isOpenGalleryImages.value = false
	emits("update:modelValue", e)
}


const onClickButton = () => {
	console.log(fileInput.value)
	fileInput.value?.click()
}

</script>

<template>
	<div>
		<button type="submit"
			@click="onClickButton"
			class="flex w-full justify-center bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
			Upload Image
		</button>
	</div>

	<div>
		<div class="w-full h-full space-y-2" @dragover="dragOver" @dragleave="dragLeave" @drop="drop">
			<div
				class="relative mt-2 flex justify-center border-dashed border border-indigo-400 shadow-lg px-6 py-5 bg-gradient-to-r hover:bg-gray-400/20">
				<label for="fileInput"
					class="absolute cursor-pointer rounded-md inset-0 focus-within:outline-none focus-within:ring-2 focus-within:ring-gray-400 focus-within:ring-offset-0">
					<input type="file" multiple name="file" id="fileInput" class="sr-only" ref="fileInput"
						@change="addComponent" />
				</label>

				<div v-if="!modelValue?.src" class="text-center">
					<div class="flex text-sm leading-6 justify-center">
						<p class="pl-1">{{ trans("Drag Images Here.") }}</p>
					</div>
					<p class="text-[0.7rem] mb-2.5">
						{{ trans("PNG, JPG, GIF up to 10MB") }}
					</p>
					<div class="mt-2.5 flex items-center justify-center gap-x-2">
						<Button id="gallery" :style="`tertiary`" :icon="'fal fa-photo-video'" label="Gallery" size="xs"
							class="relative hover:text-gray-700" @click="isOpenGalleryImages = true" />
					</div>
				</div>
				<div v-else>
					<Image :src="modelValue?.src"
						class="w-full object-cover h-full object-center group-hover:opacity-75">
					</Image>

					<div class="absolute top-0 right-0 m-2">
						<Button id="gallery" :style="`tertiary`" :icon="'fal fa-photo-video'" size="xs"
							class="relative hover:text-gray-700" @click="isOpenGalleryImages = true" />
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="mt-8">
		<div class="flex justify-between mb-2 text-gray-500 text-xs font-semibold">
			<div>Image URL</div>
			<div>100 x 250</div>
		</div>
		<IconField>
			<InputIcon>
				<FontAwesomeIcon :icon="faLink" />
			</InputIcon>
			<InputText v-model="modelValue.url" />
		</IconField>
	</div>

	<div class="mt-8 ">
		<div class="flex justify-between mb-2 text-gray-500 text-xs font-semibold">
			<div>Width</div>
		</div>
		<div class="w-full flex items-center space-x-4">
			<Slider v-model="modelValue.width" class="w-[85%] transition-all duration-300 ease-in-out" />
			<div class="text-xs font-bold text-gray-500">{{ modelValue?.width }}%</div>
		</div>
	</div>

	<div class="mt-8">
		<div class="flex justify-between mb-2 text-gray-500 text-xs font-semibold">
			<div>Alternate Text</div>
		</div>
		<InputText v-model="modelValue.alt" class="w-full" />
	</div>

	<div class="mt-8">
		<div v-if="modelValue?.properties?.border" class="border-t border-gray-300">
			<div class="my-2 text-gray-500 text-xs font-semibold">{{ trans('Border') }}</div>

			<BorderProperty v-model="modelValue.properties.border" />
		</div>

		<div v-if="modelValue?.properties?.padding" class="border-t border-gray-300">
			<div class="my-2 text-gray-500 text-xs font-semibold">{{ trans('Padding') }}</div>

			<PaddingMarginProperty v-model="modelValue.properties.padding" />
		</div>

		<div v-if="modelValue?.properties?.margin" class="border-t border-gray-300">
			<div class="my-2 text-gray-500 text-xs font-semibold">{{ trans('Margin') }}</div>

			<PaddingMarginProperty v-model="modelValue.properties.margin" />
		</div>
	</div>


	<Gallery :open="isOpenGalleryImages" @on-close="isOpenGalleryImages = false" :uploadRoutes="''"
		@onPick="onPickImage" :tabs="['images_uploaded', 'stock_images']" />
</template>

<style scoped></style>
