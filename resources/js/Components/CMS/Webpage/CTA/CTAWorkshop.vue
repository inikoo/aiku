<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faCube, faLink, faImage } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref } from "vue"
import Editor from "@/Components/Forms/Fields/BubleTextEditor/EditorV2.vue"
import Image from "@/Components/Image.vue"
import Gallery from "@/Components/Fulfilment/Website/Gallery/Gallery.vue"
import { getStyles } from "@/Composables/styles"

library.add(faCube, faLink, faImage)

const props = defineProps<{
	modelValue: any
	webpageData?: any
	blockData?: Object
}>()

console.log(props)

const emits = defineEmits<{
	(e: "update:modelValue", value: string): void
	(e: "autoSave"): void
}>()

const openGallery = ref(false)

const setImage = (e) => {
	openGallery.value = false
	emits("update:modelValue", { ...props.modelValue, image: e })
	emits("autoSave")
}

const onUpload = (e) => {
	// Assuming e.data contains the files, verify this structure in your context
	if (e.data && e.data.length <= 1) {
		openGallery.value = false
		emits("update:modelValue", { ...props.modelValue, image: e.data[0] })
		emits("autoSave")
	} else {
		console.error("No files or multiple files detected.")
	}
}
</script>

<template>
	<div class="relative" :style="getStyles(modelValue.container.properties)">
		<div
			class="relative h-80 overflow-hidden md:absolute md:left-0 md:h-full md:w-1/3 lg:w-1/2">
			<!-- Edit button to open the gallery, conditionally shown based on isEditable -->
			<button
				@click.stop="openGallery.value = !openGallery.value"
				style="position: absolute; top: 10px; left: 10px; z-index: 10">
				<FontAwesomeIcon :icon="faImage" class="text-lg h-4 text-indigo-500" />
			</button>

			<a
				:href="modelValue?.image?.url || '#'"
				target="_blank"
				rel="noopener noreferrer"
				class="block h-full w-full">
				<template v-if="modelValue?.image?.source">
					<Image
						:src="modelValue.image.source"
						:alt="modelValue.image.alt || 'image'"
						class="h-full w-full object-cover" />
				</template>
				<template v-else>
					<img
						src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/content/content-gallery-3.png"
						alt="Informative Image"
						class="h-full w-full object-cover" />
				</template>
			</a>
		</div>

		<div class="py-16 sm:py-32 lg:px-8 lg:py-40">
			<div class="pl-6 pr-6 md:ml-auto md:w-2/3 md:pl-16 lg:w-1/2 lg:pl-24 lg:pr-0 xl:pl-32">
				<Editor
					v-if="modelValue?.text"
					v-model="modelValue.text"
					@update:modelValue="() => emits('autoSave')"
					class="mb-4" />
				<div>
					<a
						:href="
							modelValue.button.link.type === 'internal'
								? modelValue.button.link.workshop
								: modelValue.button.link.href
						"
						:target="modelValue.button.link.target"
						:style="getStyles(modelValue.button.container.properties)"
						class="mt-10 flex items-center justify-center w-64 mx-auto gap-x-6">
						{{ modelValue.button.text }}
					</a>
				</div>
			</div>
		</div>
	</div>

	<Gallery
		:open="openGallery"
		@on-close="openGallery = false"
		:uploadRoutes="
			route(webpageData?.images_upload_route.name, { modelHasWebBlocks: blockData?.id })
		"
		@onPick="setImage"
		@onUpload="onUpload">
	</Gallery>
</template>
