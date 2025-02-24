<script setup lang="ts">
import { ref, nextTick, onMounted } from "vue"
import MobileMenu from "@/Components/MobileMenu.vue"
import Menu from "primevue/menu"
import { getStyles } from "@/Composables/styles"

import { faPresentation, faCube, faText, faPaperclip } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import {
	faChevronRight,
	faSignOutAlt,
	faShoppingCart,
	faSearch,
	faChevronDown,
	faTimes,
	faPlusCircle,
	faBars,
	faUserCircle,
	faImage,
	faSignInAlt,
	faFileAlt,
} from "@fas"
import { faHeart } from "@far"
import Image from "@/Components/Image.vue"
import { checkVisible, textReplaceVariables } from "@/Composables/Workshop"
import Editor from "@/Components/Forms/Fields/BubleTextEditor/EditorV2.vue"
import Moveable from "vue3-moveable"

library.add(
	faPresentation,
	faCube,
	faText,
	faImage,
	faPaperclip,
	faChevronRight,
	faSignOutAlt,
	faShoppingCart,
	faHeart,
	faSearch,
	faChevronDown,
	faTimes,
	faPlusCircle,
	faBars,
	faUserCircle,
	faSignInAlt,
	faFileAlt
)

// Define Props & Events
const props = defineProps<{
	modelValue: {
		headerText: string
		chip_text: string
		text: {
			properties: {
				position: {
					top: string
					left: string
				}
				width: string
				height: string
			}
			text: string
		}
	}
	loginMode: boolean
}>()

const emits = defineEmits<{
	(e: "update:modelValue", value: string | number): void
	(e: "setPanelActive", value: string | number): void
	(e: "autoSave"): void
}>()

const _menu = ref()
const _textRef = ref<HTMLElement | null>(null) // Correct reference type

// Ensure Moveable gets the correct target
onMounted(() => {
	nextTick(() => {
		if (_textRef.value) {
			_textRef.value = _textRef.value
		}
	})
})

// Save function to emit autoSave
function onSave() {
	emits("autoSave")
}

// Dragging function for Moveable
function onDragText(e) {
	const { target, beforeTranslate } = e
	if (beforeTranslate) {
		// Apply the movement
		target.style.transform = `translate(${beforeTranslate[0]}px, ${beforeTranslate[1]}px)`

		// Update modelValue position
		props.modelValue.text.properties.position.left = `${beforeTranslate[0]}px`
		props.modelValue.text.properties.position.top = `${beforeTranslate[1]}px`
		onSave()
	}
}

// Resizing function for Moveable
function onResizeText(e) {
	const { target, width, height } = e
	target.style.width = `${width}px`
	target.style.height = `${height}px`

	// Update modelValue
	props.modelValue.text.properties.width = `${width}px`
	props.modelValue.text.properties.height = `${height}px`
	onSave()
}

// Scaling function (Fixing potential errors)
function onTextScale({ offsetHeight = 100, offsetWidth = 100, transform }) {
	if (!transform) return

	const scaleMatch = transform.match(/scale\(([^)]+)\)/)
	if (!scaleMatch) return

	const scaleValues = scaleMatch[1].split(", ").map(Number)
	props.modelValue.text.properties.width = `${offsetWidth * scaleValues[0]}px`
	props.modelValue.text.properties.height = `${offsetHeight * scaleValues[1]}px`
	onSave()
}

// Toggle menu
const toggle = (event) => {
	_menu.value.toggle(event)
}

// Make editor editable
const editable = ref(true)
</script>

<template>
	<div class="shadow-sm" :style="getStyles(modelValue.container.properties)">
		<div class="flex flex-col justify-between items-center py-4 px-6 hidden lg:block">
			<div class="w-full grid grid-cols-3 items-center gap-6">
				<!-- Logo -->
				<div
					:style="getStyles(modelValue.logo.properties)"
					@click="() => emits('setPanelActive', 'logo')">
					<Image
						:alt="modelValue?.logo?.alt"
						:src="modelValue?.logo?.image?.source"
						class="hover-dashed"></Image>
				</div>

				<!-- Search Bar -->
				<div class="relative justify-self-center w-full max-w-md"></div>

				<!-- Text (Movable & Resizable) -->
				<div ref="_textRef" class="relative resizable-box">
					<Editor
						v-model="modelValue.text.text"
						:editable="editable"
						@update:model-value="
							(e) => {
								modelValue.text.text = e
								emits('update:modelValue', modelValue)
							}
						" />
				</div>

				<Moveable
					class="moveable"
					:target="_textRef"
					:draggable="true"
					:resizable="true"
					:scalable="true"
					:keepRatio="false"
					:throttleDrag="1"
					:throttleResize="1"
					:renderDirections="['nw', 'n', 'ne', 'w', 'e', 'sw', 's', 'se']"
					@drag="onDragText"
					@resize="onResizeText"
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
		</div>

		<!-- Mobile view (hidden on desktop) -->
		<div class="block md:hidden p-3">
			<div class="flex justify-between items-center">
				<MobileMenu :header="modelValue" :menu="modelValue" />

				<!-- Logo for Mobile -->
				<img
					v-if="!modelValue.logo"
					src="https://d19ayerf5ehaab.cloudfront.net/assets/store-18687/18687-logo-1642004490.png"
					alt="Ancient Wisdom Logo"
					class="h-10 mx-2" />

				<Image v-else :src="modelValue?.logo?.source" class="h-10 mx-2"></Image>

				<!-- Profile Icon with Dropdown Menu -->
				<div @click="toggle" class="flex items-center cursor-pointer text-white">
					<FontAwesomeIcon icon="fas fa-user-circle" class="text-2xl" />
					<Menu ref="_menu" id="overlay_menu" :model="items" :popup="true">
						<template #itemicon="{ item }">
							<FontAwesomeIcon :icon="item.icon" />
						</template>
					</Menu>
				</div>
			</div>
		</div>
	</div>
</template>

<style scoped>
.moveable {
	z-index: 9999;
	position: absolute;
}

.resizable-box {
	display: inline-block;
	background: #f0f0f0;
	padding: 5px;
	border: 1px solid #ccc;
}
</style>
