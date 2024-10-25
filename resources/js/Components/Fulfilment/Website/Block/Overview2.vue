<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faCube, faLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref } from "vue"
import Editor from "@/Components/Forms/Fields/BubleTextEditor/EditorV2.vue"
import Image from "@/Components/Image.vue"
import Gallery from "@/Components/Fulfilment/Website/Gallery/Gallery.vue"
import { getStyles } from "@/Composables/styles"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faImage, faEdit } from "@far"
import GalleryManagement from "@/Components/Utils/GalleryManagement/GalleryManagement.vue"
import Modal from "@/Components/Utils/Modal.vue"
import Moveable from "vue3-moveable"

library.add(faCube, faLink)

const props = defineProps<{
	modelValue: any
	webpageData: any
	web_block: Object
	id: Number
	type: String
	isEditable?: boolean
	properties: {}
}>()

const emits = defineEmits<{
	(e: "update:modelValue", value: string): void
	(e: "autoSave"): void
}>()

const activeTextIndex = ref(-1)
const activeImageIndex = ref(-1)
const activeImageIndexModal = ref(-1)
const isModalGallery = ref(false)

function onDragImage({ top = 0, bottom = 0, left = 0, right = 0 }) {
	props.modelValue.images[activeImageIndex.value].properties.position.top = `${top}px`
	props.modelValue.images[activeImageIndex.value].properties.position.bottom = `${bottom}px`
	props.modelValue.images[activeImageIndex.value].properties.position.left = `${left}px`
	props.modelValue.images[activeImageIndex.value].properties.position.right = `${right}px`
	onSave()
}

function onImageScale({ offsetHeight = 100, offsetWidth = 100, transform }) {
	let transformString = transform
	let transformVal = transformString
		.match(/scale\(([^)]+)\)/)[1]
		.split(", ")
		.map(Number)
	props.modelValue.images[activeImageIndex.value].properties.width = `${
		offsetWidth * transformVal[0]
	}px`
	props.modelValue.images[activeImageIndex.value].properties.height = `${
		offsetHeight * transformVal[1]
	}px`
	onSave()
}

const onChangeImage = (image) => {
	const data = {...props.modelValue}
	data.images[activeImageIndexModal.value].sources = {...image[0].source}
	isModalGallery.value = false
	activeImageIndexModal.value = -1
	props.modelValue = data
	onSave()
}

function onSave() {
	emits("autoSave")
}

function activateMoveableImage(index: number) {
	console.log(index,'das',activeImageIndex.value);
	
	activeImageIndex.value = index
	activeTextIndex.value = -1
}
</script>

<template>
	<div class="container flex flex-wrap justify-between" :styles="getStyles(modelValue.container.properties)">
		<!-- Image Section -->
		<div class="imgBx relative w-1/2  bg-gray-900 transition-all duration-300">
			<div  v-for="(image, index) in modelValue.images" :key="index">
				<div
					class="absolute"
					:class="`image-${index}`"
					@dblclick="activateMoveableImage(index)"
					ref="el => imageRefs[index] = el"
					:style="{
						width: image?.properties?.width ? `${image?.properties?.width}` : 'auto',
						height: image?.properties?.height ? `${image?.properties?.height}` : 'auto',
						top: image?.properties?.position?.top
							? `${image?.properties?.position?.top}`
							: 'auto',
						left: image?.properties?.position?.left
							? `${image?.properties?.position?.left}`
							: 'auto',
					}">
				
					<button
						@click="
							() => {
								isModalGallery = true
								activeImageIndexModal = index
							}
						"
						style="position: absolute; top: 0; left: 10px; z-index: 10">
						<FontAwesomeIcon :icon="faImage" class="text-lg h-4 text-indigo-500" />
					</button>
					
					<Image :src="image.source" />
				</div>
<pre>{{index}}</pre>
				<Moveable
					v-if="activeImageIndex === index"
					class="moveable"
					:target="[`.image-${index}`]"
					:draggable="true"
					:scalable="true"
					@drag="onDragImage"
					@scale="onImageScale"
					:snapDirections="{ top: true, left: true, bottom: true, right: true }"
					:elementSnapDirections="{
						top: true,
						left: true,
						bottom: true,
						right: true,
						center: true,
						middle: true,
					}" />
			</div>
		</div>

		<!-- Details Section -->
		<div class="details flex flex-col justify-center w-1/2 p-10">
            <Editor v-model="modelValue.text" />
		</div>
	</div>
	<Modal :isOpen="isModalGallery" @onClose="() => (isModalGallery = false)" width="w-3/4">
		<GalleryManagement
			:maxSelected="1"
			:uploadRoute="{
				...webpageData.images_upload_route,
				parameters: id,
			}"
			:closePopup="() => (isModalGallery = false)"
			@submitSelectedImages="onChangeImage" />
	</Modal>
</template>
