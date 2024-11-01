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
const activeImageIndexModal = ref(null)
const isModalGallery = ref(false)

function onSave() {
	emits("autoSave")
}

const onChangeImage = (image) => {
	const data = { ...props.modelValue };
	console.log(activeImageIndexModal.value,'asd');
	
	if (activeImageIndexModal.value === 'column1') {
		data.column1.source = image[0].source;
	} else if (activeImageIndexModal.value === 'column2') {
		data.column2.source = image[0].source;
	} else if (activeImageIndexModal.value === 'column3') {
		data.column3.source = image[0].source;
	} else if (activeImageIndexModal.value === 'column4') {
		data.column4.source = image[0].source;
	}

	props.modelValue = data;
	isModalGallery.value = false;
	activeImageIndexModal.value = null;

	onSave();
};
</script>

<template>
	<pre>{{ modelValue }}</pre>
	<div :style="getStyles(modelValue?.container?.properties)">
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
							<div
								v-if="
									!modelValue.column1.source 
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
											if (isEditable) isModalGallery = !isModalGallery
											activeImageIndexModal = 'column1'
										}
									"
									style="position: absolute; top: 0; left: 10px; z-index: 10">
									<FontAwesomeIcon
										:icon="faImage"
										class="text-lg h-4 text-indigo-500" />
								</button>
							</div>

							<!-- Images Structure (renders only if images are present) -->
							<div v-else class="absolute">
								<button
									@click="
										() => {
											if (isEditable) isModalGallery = !isModalGallery
											activeImageIndexModal = 'column1'
										}
									"
									style="position: absolute; top: 0; left: 10px; z-index: 10">
									<FontAwesomeIcon
										:icon="faImage"
										class="text-lg h-4 text-indigo-500" />
								</button>
								<Image
									:src="modelValue.column1.source"
									class="w-full h-full object-cover rounded-lg" />
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
								v-if="!modelValue.column2.source"
								class="relative w-full max-lg:max-w-xs">
								<img
									class="w-full object-cover rounded-lg shadow-lg"
									src="https://tailwindui.com/plus/img/component-images/bento-03-performance.png"
									alt="Default Performance Image" />

								<!-- Button to Change Default Image -->
								<button
									@click="
										() => {
											if (isEditable) isModalGallery = !isModalGallery
											activeImageIndexModal = 'column2'
										}
									"
									style="position: absolute; top: 0; left: 10px; z-index: 10">
									<FontAwesomeIcon
										:icon="faImage"
										class="text-lg h-4 text-indigo-500" />
								</button>
							</div>

							<!-- Render Column2 Image (when present) -->
							<div v-else class="relative w-full max-lg:max-w-xs">
								<button
									@click="
										() => {
											if (isEditable) isModalGallery = !isModalGallery
											activeImageIndexModal = 'column2'
										}
									"
									style="position: absolute; top: 0; left: 10px; z-index: 10">
									<FontAwesomeIcon
										:icon="faImage"
										class="text-lg h-4 text-indigo-500" />
								</button>
								<Image
									:src="modelValue.column2.source"
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
								v-if="!modelValue.column3.source"
								class="relative w-full max-lg:max-w-xs">
								<img
									class="h-[min(152px,40cqw)] object-cover object-center rounded-lg shadow-lg"
									src="https://tailwindui.com/plus/img/component-images/bento-03-security.png"
									alt="Default Security Image" />

								<!-- Button to Change Default Image -->
								<button
									@click="
										() => {
											if (isEditable) isModalGallery = !isModalGallery
											activeImageIndexModal = 'column3'
										}
									"
									style="position: absolute; top: 0; left: 10px; z-index: 10">
									<FontAwesomeIcon
										:icon="faImage"
										class="text-lg h-4 text-indigo-500" />
								</button>
							</div>

							<!-- Render Column3 Image (when present) -->
							<div v-else class="relative w-full max-lg:max-w-xs">
								<button
									@click="
										() => {
											if (isEditable) isModalGallery = !isModalGallery
											activeImageIndexModal = 'column3'
										}
									"
									style="position: absolute; top: 0; left: 10px; z-index: 10">
									<FontAwesomeIcon
										:icon="faImage"
										class="text-lg h-4 text-indigo-500" />
								</button>
								<Image
									:src="modelValue.column3.source"
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
								v-if="!modelValue.column4.source"
								class="absolute inset-x-10 bottom-0 top-10 overflow-hidden rounded-t-[12cqw] border-x-[3cqw] border-t-[3cqw] border-gray-700 bg-gray-900 shadow-2xl">
								<img
									class="size-full object-cover object-top"
									src="https://tailwindui.com/plus/img/component-images/bento-03-mobile-friendly.png"
									alt="Default Mobile Friendly Image" />

								<!-- Button to Change Default Image -->
								<button
									@click="
										() => {
											if (isEditable) isModalGallery = !isModalGallery
											activeImageIndexModal = 'column4'
										}
									"
									style="position: absolute; top: 0; left: 10px; z-index: 10">
									<FontAwesomeIcon
										:icon="faImage"
										class="text-lg h-4 text-indigo-500" />
								</button>
							</div>

							<!-- Render Column4 Image (when present) -->
							<div
								v-else
								class="absolute ">
								<button
									@click="
										() => {
											if (isEditable) isModalGallery = !isModalGallery
											activeImageIndexModal = 'column4'
										}
									"
									style="position: absolute; top: 0; left: 10px; z-index: 10">
									<FontAwesomeIcon
										:icon="faImage"
										class="text-lg h-4 text-indigo-500" />
								</button>
								<Image
									:src="modelValue.column4.source"
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
