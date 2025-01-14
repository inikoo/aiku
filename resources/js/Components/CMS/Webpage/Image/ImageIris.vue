<script setup lang="ts">
import { faCube, faStar, faImage } from "@fas"
import { faPencil } from "@far"
import { library } from "@fortawesome/fontawesome-svg-core"
import Image from "@/Components/Image.vue"
import { getStyles } from "@/Composables/styles"
import { ref } from "vue"

library.add(faCube, faStar, faImage, faPencil)

const props = defineProps<{
	fieldValue: {
		value: {
			images: {
				source: string
				link_data: {
					url: string
				}
			}[]
			layout_type: string
		}
	}
	webpageData?: any
	web_block?: Object
	id?: Number
	type?: String
	isEditable?: boolean
}>()

const getHref = (index: number) => {
	const image = props.fieldValue?.value?.images?.[index]

	if (image?.link_data?.url) {
		return image.link_data.url
	}

	return null
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
	<div v-if="fieldValue?.value?.images" class="flex flex-wrap" :style="getStyles(fieldValue.container.properties)">
		<div
			v-for="index in getImageSlots(fieldValue?.value?.layout_type)"
			:key="index"
			class="relative p-2"
			:class="getColumnWidthClass(fieldValue?.value?.layout_type, index - 1)"
		>
			<a
				v-if="fieldValue?.value?.images?.[index - 1]?.source"
				:href="getHref(index - 1)"
				target="_blank"
				rel="noopener noreferrer"
				class="transition-shadow aspect-h-1 aspect-w-1 w-full">
				<Image
					:src="fieldValue?.value?.images?.[index - 1]?.source"
					class="w-full object-cover object-center group-hover:opacity-75" />
			</a>
		</div>
	</div>

</template>
