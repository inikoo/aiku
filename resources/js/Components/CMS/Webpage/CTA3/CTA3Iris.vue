<script setup lang="ts">
import { ref } from "vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Editor2 from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"
import Editor from "@/Components/Forms/Fields/BubleTextEditor/EditorV2.vue"
import Image from "@/Components/Image.vue"
import Gallery from "@/Components/Fulfilment/Website/Gallery/Gallery.vue"
import { getStyles } from "@/Composables/styles"

const props = defineProps<{
	fieldValue: any
	webpageData?: any
	blockData?: Object
}>()

const emits = defineEmits<{
	// (e: "update:fieldValue", value: string): void
	(e: "autoSave"): void
}>()

const openGallery = ref(false)


</script>

<template> 
	<div class="relative overflow-hidden rounded-lg lg:h-96" :style="getStyles(fieldValue.container.properties)">
		<div class="absolute inset-0">
			<img :src="fieldValue.container.properties.background.image.source ? fieldValue.container.properties.background.image.source.original : fieldValue.image" alt="" class="h-full w-full object-cover object-center" />
		</div>

		<div aria-hidden="true" class="relative h-96 w-full lg:hidden" />
		<div aria-hidden="true" class="relative h-32 w-full lg:hidden" />

		<div
			class="absolute inset-x-0 bottom-0 rounded-bl-lg rounded-br-lg bg-white bg-opacity-75 p-6 backdrop-blur backdrop-filter sm:flex sm:items-center sm:justify-between lg:inset-x-auto lg:inset-y-0 lg:w-96 lg:flex-col lg:items-start lg:rounded-br-none lg:rounded-tl-lg">
			<div class="text-gray-600 pr-3 overflow-y-auto mb-4">
					<div v-html="fieldValue.text" />
			</div>

			<div
				typeof="button"
				:style="getStyles(fieldValue.button.container.properties)"
				class="mt-10 flex items-center justify-center w-64 mx-auto gap-x-6">
				{{ fieldValue.button.text }}
			</div>
		</div>
	</div>
</template>
