<script setup lang="ts">
import { faCube, faLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref } from "vue"
import Editor from "@/Components/Forms/Fields/BubleTextEditor/EditorV2.vue"
import Image from "@/Components/Image.vue"
import { getStyles } from "@/Composables/styles"
import Modal from "@/Components/Utils/Modal.vue"

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faImage, faEdit } from "@far"
import GalleryManagement from "@/Components/Utils/GalleryManagement/GalleryManagement.vue"
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

function onSave() {
	emits("autoSave")
}

const onChangeImage = (image) => {
	const data = { ...props.modelValue }
	data.images[activeImageIndexModal.value].sources = { ...image[0].source }
	isModalGallery.value = false
	activeImageIndexModal.value = -1
	props.modelValue = data
	onSave()
}

function activateMoveableImage(index: number) {
	activeImageIndex.value = index
	activeTextIndex.value = -1
}
</script>

<template>
	<div :style="getStyles(modelValue.container.properties)">
		<div class="mx-auto max-w-2xl px-6 lg:max-w-7xl lg:px-8">
			<Editor
				v-model="modelValue.title"
				:editable="isEditable"
				@update:modelValue="() => emits('autoSave')" />
			<div class="mt-10 grid gap-4 sm:mt-16 lg:grid-cols-3 lg:grid-rows-2">
				<div class="relative lg:row-span-2">
					<div class="absolute inset-px rounded-lg bg-white lg:rounded-l-[2rem]" />
					<div
						class="relative flex h-full flex-col overflow-hidden rounded-[calc(theme(borderRadius.lg)+1px)] lg:rounded-l-[calc(2rem+1px)]">
						<div class="px-8 pb-3 pt-8 sm:px-10 sm:pb-0 sm:pt-10">
							<Editor
								v-model="modelValue.column1.text"
								:editable="isEditable"
								@update:modelValue="() => emits('autoSave')" />
						</div>
						<div
							class="relative min-h-[30rem] w-full grow [container-type:inline-size] max-lg:mx-auto max-lg:max-w-sm">
							<!-- Default Placeholder Structure (renders only if no images are present) -->
							<div
								v-if="
									!modelValue.column1.images || !modelValue.column1.images.length
								"
								class="absolute inset-x-10 bottom-0 top-10 overflow-hidden rounded-t-[12cqw] border-x-[3cqw] border-t-[3cqw] border-gray-700 bg-gray-900 shadow-2xl">
								<img
									class="size-full object-cover object-top"
									src="https://tailwindui.com/plus/img/component-images/bento-03-mobile-friendly.png"
									alt="Default Image" />

								<!-- Button to Change Default Image -->
								<button
									@click="
										() => {
											isModalGallery = true
											activeImageIndexModal = 0
										}
									"
									style="position: absolute; top: 0; left: 10px; z-index: 10">
						<FontAwesomeIcon :icon="faImage" class="text-lg h-4 text-indigo-500" />
								</button>
							</div>

							<!-- Images Structure (renders only if images are present) -->
							<div
								v-else
								v-for="(image, index) in modelValue.column1.images"
								:key="index"
								class="absolute"
								:class="`image-${index}`"
								@dblclick="activateMoveableImage(index)"
								ref="el => imageRefs[index] = el"
								:style="{
									width: image?.properties?.width || 'auto',
									height: image?.properties?.height || 'auto',
									top: image?.properties?.position?.top || 'auto',
									left: image?.properties?.position?.left || 'auto',
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
								<Image
									:src="image.sources"
									class="w-full h-full object-cover rounded-lg" />

								<!-- Moveable Component for the Image -->
								<Moveable
									v-if="activeImageIndex === index"
									class="moveable"
									:target="[`.image-${index}`]"
									:draggable="true"
									:scalable="true"
									@drag="onDragImage"
									@scale="onImageScale"
									:snapDirections="{
										top: true,
										left: true,
										bottom: true,
										right: true,
									}"
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
					</div>
					<div
						class="pointer-events-none absolute inset-px rounded-lg shadow ring-1 ring-black/5 lg:rounded-l-[2rem]" />
				</div>
				<!-- Column 2 -->
				<div class="relative max-lg:row-start-1">
					<div
						class="absolute inset-px rounded-lg bg-white max-lg:rounded-t-[2rem]"></div>

					<!-- Conditional Content Wrapper for Column 2 -->
					<div
						class="relative flex h-full flex-col overflow-hidden rounded-[calc(theme(borderRadius.lg)+1px)] max-lg:rounded-t-[calc(2rem+1px)]">
						<!-- Text Editor Section -->
						<div class="px-8 pt-8 sm:px-10 sm:pt-10">
							<Editor
								v-model="modelValue.column2.text"
								:editable="isEditable"
								@update:modelValue="() => emits('autoSave')" />
						</div>

						<!-- Conditional Image or Default Structure for Column 2 -->
						<div
							class="flex flex-1 items-center justify-center px-8 max-lg:pb-12 max-lg:pt-10 sm:px-10 lg:pb-2">
							<!-- Default Image Structure (renders if column2.image is absent) -->
							<div
								v-if="!modelValue.column2.image"
								class="relative w-full max-lg:max-w-xs">
								<img
									class="w-full object-cover rounded-lg shadow-lg"
									src="https://tailwindui.com/plus/img/component-images/bento-03-performance.png"
									alt="Default Performance Image" />

								<!-- Button to Change Default Image -->
								<button
									@click="
										() => {
											isModalGallery = true
											activeImageIndexModal = 1
										}
									"
                  style="position: absolute; top: 0; left: 10px; z-index: 10">
                  <FontAwesomeIcon :icon="faImage" class="text-lg h-4 text-indigo-500" />
								</button>
							</div>

							<!-- Render Column2 Image (when present) -->
							<div v-else class="relative w-full max-lg:max-w-xs">
								<button
									@click="
										() => {
											isModalGallery = true
											activeImageIndexModal = 1
										}
									"
                  style="position: absolute; top: 0; left: 10px; z-index: 10">
                  <FontAwesomeIcon :icon="faImage" class="text-lg h-4 text-indigo-500" />
								</button>
								<img
									:src="modelValue.column2.image.sources"
									class="w-full object-cover rounded-lg shadow-lg" />
							</div>
						</div>
					</div>

					<div
						class="pointer-events-none absolute inset-px rounded-lg shadow ring-1 ring-black/5 max-lg:rounded-t-[2rem]"></div>
				</div>

				<!-- Column 3 -->
				<div class="relative max-lg:row-start-3 lg:col-start-2 lg:row-start-2">
					<div class="absolute inset-px rounded-lg bg-white"></div>

					<!-- Conditional Content Wrapper for Column 3 -->
					<div
						class="relative flex h-full flex-col overflow-hidden rounded-[calc(theme(borderRadius.lg)+1px)]">
						<!-- Text Editor Section -->
						<div class="px-8 pt-8 sm:px-10 sm:pt-10">
							<Editor
								v-model="modelValue.column3.text"
								:editable="isEditable"
								@update:modelValue="() => emits('autoSave')" />
						</div>

						<!-- Conditional Image or Default Structure for Column 3 -->
						<div
							class="flex flex-1 items-center justify-center px-8 max-lg:py-6 lg:pb-2">
							<!-- Default Image Structure (renders if column3.image is absent) -->
							<div
								v-if="!modelValue.column3.image"
								class="relative w-full max-lg:max-w-xs">
								<img
									class="h-[min(152px,40cqw)] object-cover object-center rounded-lg shadow-lg"
									src="https://tailwindui.com/plus/img/component-images/bento-03-security.png"
									alt="Default Security Image" />

								<!-- Button to Change Default Image -->
								<button
									@click="
										() => {
											isModalGallery = true
											activeImageIndexModal = 2
										}
									"
									style="position: absolute; top: 0; left: 10px; z-index: 10">
						<FontAwesomeIcon :icon="faImage" class="text-lg h-4 text-indigo-500" />
								</button>
							</div>

							<!-- Render Column3 Image (when present) -->
							<div v-else class="relative w-full max-lg:max-w-xs">
								<button
									@click="
										() => {
											isModalGallery = true
											activeImageIndexModal = 2
										}
									"
                  style="position: absolute; top: 0; left: 10px; z-index: 10">
                  <FontAwesomeIcon :icon="faImage" class="text-lg h-4 text-indigo-500" />
								</button>
								<img
									:src="modelValue.column3.image.sources"
									class="h-[min(152px,40cqw)] object-cover object-center rounded-lg shadow-lg" />
							</div>
						</div>
					</div>

					<div
						class="pointer-events-none absolute inset-px rounded-lg shadow ring-1 ring-black/5"></div>
				</div>

				<!-- Column 4 -->
				<div class="relative lg:row-span-2">
					<div class="absolute inset-px rounded-lg bg-white lg:rounded-r-[2rem]"></div>

					<!-- Conditional Content Wrapper for Column 4 -->
					<div
						class="relative flex h-full flex-col overflow-hidden rounded-[calc(theme(borderRadius.lg)+1px)] lg:rounded-r-[calc(2rem+1px)]">
						<div class="px-8 pb-3 pt-8 sm:px-10 sm:pb-0 sm:pt-10">
							<Editor
								v-model="modelValue.column4.text"
								:editable="isEditable"
								@update:modelValue="() => emits('autoSave')" />
						</div>

						<!-- Conditional Image or Default Structure for Column 4 -->
						<div
							class="relative min-h-[30rem] w-full grow [container-type:inline-size] max-lg:mx-auto max-lg:max-w-sm">
							<!-- Default Image Structure (renders if column4.image is absent) -->
							<div
								v-if="!modelValue.column4.image"
								class="absolute inset-x-10 bottom-0 top-10 overflow-hidden rounded-t-[12cqw] border-x-[3cqw] border-t-[3cqw] border-gray-700 bg-gray-900 shadow-2xl">
								<img
									class="size-full object-cover object-top"
									src="https://tailwindui.com/plus/img/component-images/bento-03-mobile-friendly.png"
									alt="Default Mobile Friendly Image" />

								<!-- Button to Change Default Image -->
								<button
									@click="
										() => {
											isModalGallery = true
											activeImageIndexModal = 3
										}
									"
                  style="position: absolute; top: 0; left: 10px; z-index: 10">
                  <FontAwesomeIcon :icon="faImage" class="text-lg h-4 text-indigo-500" />
								</button>
							</div>

							<!-- Render Column4 Image (when present) -->
							<div
								v-else
								class="absolute inset-x-10 bottom-0 top-10 overflow-hidden rounded-t-[12cqw] border-x-[3cqw] border-t-[3cqw] border-gray-700 bg-gray-900 shadow-2xl">
								<button
									@click="
										() => {
											isModalGallery = true
											activeImageIndexModal = 3
										}
									"
                  style="position: absolute; top: 0; left: 10px; z-index: 10">
                  <FontAwesomeIcon :icon="faImage" class="text-lg h-4 text-indigo-500" />
								</button>
								<img
									:src="modelValue.column4.image.sources"
									class="size-full object-cover object-top" />
							</div>
						</div>
					</div>

					<div
						class="pointer-events-none absolute inset-px rounded-lg shadow ring-1 ring-black/5 lg:rounded-r-[2rem]"></div>
				</div>
			</div>
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
