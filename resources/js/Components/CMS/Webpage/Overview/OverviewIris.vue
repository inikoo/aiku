<script setup lang="ts">
import Image from "@/Components/Image.vue"
import { ref, onMounted, onBeforeUnmount } from "vue"
import Moveable from "vue3-moveable"
import Editor from "@/Components/Forms/Fields/BubleTextEditor/EditorV2.vue"
import { getStyles } from "@/Composables/styles"
import GalleryManagement from "@/Components/Utils/GalleryManagement/GalleryManagement.vue"
import Modal from "@/Components/Utils/Modal.vue"

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faImage, faEdit } from "@far"

const props = defineProps<{
    fieldValue: {
		
	}
	webpageData?: any
	blockData?: Object
}>()

</script>

<template>
	<div
		ref="_parentComponent"
		class="relative isolate transition-all"
		:style="getStyles(fieldValue.container.properties)">
		<!-- Render text elements -->
		<div v-for="(text, index) in fieldValue.texts.values" :key="index">
			<div
				class="absolute"
				:class="`text-${index}`"
				ref="el => textRefs[index] = el"
				:style="{
					width: text?.properties?.width ? `${text?.properties?.width}` : 'auto',
					height: text?.properties?.height ? `${text?.properties?.height}` : 'auto',
					top: text?.properties?.position?.top
						? `${text?.properties?.position?.top}`
						: 'auto',
					left: text?.properties?.position?.left
						? `${text?.properties?.position?.left}`
						: 'auto',
				}">
				<div v-html="text.text" />
			</div>
		</div>

		<!-- Render image elements -->
		<div>
			<div v-for="(image, index) in fieldValue.images" :key="index">
				<div
					class="absolute"
					:class="`image-${index}`"
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
					<Image :src="image.sources" />
				</div>
			</div>
		</div>
	</div>
</template>
