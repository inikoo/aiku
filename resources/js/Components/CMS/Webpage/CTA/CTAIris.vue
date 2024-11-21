<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faCube, faLink, faImage } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import Image from "@/Components/Image.vue"
import { getStyles } from "@/Composables/styles"

library.add(faCube, faLink, faImage)

const props = defineProps<{
	modelValue: any
	webpageData: any
	blockData: Object
}>()


</script>

<template>
	<div class="relative" :style="getStyles(modelValue.container.properties)">
		<div
			class="relative h-80 overflow-hidden bg-indigo-600 md:absolute md:left-0 md:h-full md:w-1/3 lg:w-1/2">
			<button
				style="position: absolute; top: 10px; left: 10px; z-index: 10">
				<FontAwesomeIcon :icon="faImage" class="text-lg h-4 text-indigo-500" />
			</button>

			<a
				v-if="!modelValue?.image?.source"
				:href="modelValue?.image?.url || '#'"
				target="_blank"
				rel="noopener noreferrer"
				class="block h-full w-full">
				<img
					src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/content/content-gallery-3.png"
					alt="Informative Image"
					class="h-full w-full object-cover" />
			</a>

			<!-- Clickable link with Image component if custom image source is available -->
			<a
				v-else
				:href="modelValue?.image?.url || '#'"
				target="_blank"
				rel="noopener noreferrer"
				class="block h-full w-full">
				<Image
					:src="modelValue?.image?.source"
					:alt="modelValue?.image?.alt"
					class="h-full w-full object-cover" />
			</a>
		</div>

		<div class=" max-w-7xl py-24 sm:py-32 lg:px-8 lg:py-40">
			<div class="pl-6 pr-6 md:ml-auto md:w-2/3 md:pl-16 lg:w-1/2 lg:pl-24 lg:pr-0 xl:pl-32">
				<div v-html="modelValue.text" />
				<div
					typeof="button"
					:style="getStyles(modelValue.button.container.properties)"
					class="mt-10 flex items-center justify-center w-64 mx-auto gap-x-6">
					{{ modelValue.button.text }}
				</div>
			</div>
		</div>
	</div>
</template>

<style lang="scss" >
</style>