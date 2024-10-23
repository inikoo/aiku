<script setup lang="ts">
import Image from "@/Components/Image.vue"
import { ref, onMounted, onBeforeUnmount } from "vue"
import Moveable from "vue3-moveable"
import Editor from "@/Components/Forms/Fields/BubleTextEditor/EditorV2.vue";

// Sample data passed as props (modelValue)
const props = withDefaults(
	defineProps<{
		modelValue?: any
		isEditable?: boolean
	}>(),
	{
		isEditable: true,
	}
)

const emits = defineEmits<{
	(e: "autoSave", payload: any): void
}>()

const textRefs = ref<Array<HTMLElement | null>>([]) // Refs for text elements
const imageRefs = ref<Array<HTMLElement | null>>([]) // Refs for image elements

const activeTextIndex = ref(-1) // Active text index
const activeImageIndex = ref(-1) // Active image index


function onDragImage({top = 0, bottom = 0, left = 0, right = 0}) {
      props.modelValue.images[activeImageIndex.value].properties.position.top = `${top}px`
      props.modelValue.images[activeImageIndex.value].properties.position.bottom = `${bottom}px`
      props.modelValue.images[activeImageIndex.value].properties.position.left =`${left}px`
      props.modelValue.images[activeImageIndex.value].properties.position.right = `${right}px`
}

function onDragText({top = 0, bottom = 0, left = 0, right = 0}) {
    props.modelValue.texts.values[activeTextIndex.value].properties.position.top = `${top}px`
    props.modelValue.texts.values[activeTextIndex.value].properties.position.bottom = `${bottom}px`
    props.modelValue.texts.values[activeTextIndex.value].properties.position.left =`${left}px`
    props.modelValue.texts.values[activeTextIndex.value].properties.position.right = `${right}px`
}

function onImageScale({ offsetHeight = 100, offsetWidth = 100, transform }) {
    let transformString = transform;
    let transformVal = transformString.match(/scale\(([^)]+)\)/)[1].split(", ").map(Number);
    props.modelValue.images[activeImageIndex.value].properties.width = `${offsetWidth * transformVal[0]}px`
    props.modelValue.images[activeImageIndex.value].properties.height = `${offsetHeight * transformVal[1]}px`
}

function onTextScale({ offsetHeight = 100, offsetWidth = 100, transform }) {
    let transformString = transform;
    let transformVal = transformString.match(/scale\(([^)]+)\)/)[1].split(", ").map(Number);
    props.modelValue.texts.values[activeTextIndex.value].properties.width = `${offsetWidth * transformVal[0]}px`
    props.modelValue.texts.values[activeTextIndex.value].properties.height = `${offsetHeight * transformVal[1]}px`
}


// Save position and size for text
function savePositionText(index: number) {
	const target = textRefs.value[index]
	if (target) {
		const rect = target.getBoundingClientRect()
		props.modelValue.texts.values[index].properties = {
			position: { left: rect.left, top: rect.top },
			size: { width: rect.width, height: rect.height },
		}
		emits("autoSave", props.modelValue)
	}
}

function savePositionImage(index: number) {
	const target = imageRefs.value[index]
	if (target) {
		const rect = target.getBoundingClientRect()
		props.modelValue.images[index].properties = {
			position: { left: rect.left, top: rect.top },
			size: { width: rect.width, height: rect.height },
		}
		emits("autoSave", props.modelValue)
	}
}

function activateMoveableText(index: number) {
	activeTextIndex.value = index
	activeImageIndex.value = -1 
}

function activateMoveableImage(index: number) {
	activeImageIndex.value = index
	activeTextIndex.value = -1 
}

function handleClickOutside() {
	activeTextIndex.value = -1
	activeImageIndex.value = -1
}

onMounted(() => {
	document.addEventListener("click", handleClickOutside)
})

onBeforeUnmount(() => {
	document.removeEventListener("click", handleClickOutside)
})
</script>

<template>
	<div
		ref="_parentComponent"
		class="relative isolate h-screen  transition-all">
		<!-- Render text elements -->
		<div v-for="(text, index) in modelValue.texts.values" :key="index">
			<div
                class="absolute"
				:class="`text-${index}`"
				@dblclick="activateMoveableText(index)"
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
				}" >
                  <Editor v-model="text.text" :editable="false"/>
            </div>
			<Moveable
				v-if="activeTextIndex === index"
				class="moveable"
				:target="[`.text-${index}`]"
				:draggable="true"
				:scalable="true"
				@drag="onDragText"
				@scale="onTextScale"
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

		<!-- Render image elements -->
		<div>
			<div v-for="(image, index) in modelValue.images" :key="index">
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
					<Image :src="image.sources" />
				</div>
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
	</div>
</template>
