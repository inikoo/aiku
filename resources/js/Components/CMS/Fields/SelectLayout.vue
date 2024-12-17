<script setup lang="ts">
import { ref, toRaw } from "vue"
import Modal from "@/Components/Utils/Modal.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faImage } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faImage)

type Layout =
	| { name: string; layout_type: string; grid: string; images: number; flex?: undefined }
	| { name: string; layout_type: string; flex: string[]; images: number; grid?: undefined }

const props = defineProps<{
	modelValue: {}
}>()
const emit = defineEmits(["update:modelValue"])
const isModalOpen = ref(false)


const layouts = ref<Layout[]>([
	{ name: "Layout Single Wide Image", layout_type: "1", grid: "grid-cols-1", images: 1 },
	{ name: "Layout Two Equal Images", layout_type: "2", grid: "grid-cols-2", images: 2 },
	{ name: "Layout Three Equal Images", layout_type: "3", grid: "grid-cols-3", images: 3 },
	{ name: "Layout Four Equal Images", layout_type: "4", grid: "grid-cols-4", images: 4 },
	{ name: "Layout 12 (1/3, 2/3)", layout_type: "12", flex: ["w-1/3", "w-2/3"], images: 2 }, // Flex layout
	{ name: "Layout 21 (2/3, 1/3)", layout_type: "21", flex: ["w-2/3", "w-1/3"], images: 2 }, // Flex layout
	{ name: "Layout 13 (1/4, 3/4)", layout_type: "13", flex: ["w-1/4", "w-3/4"], images: 2 }, // Flex layout
	{ name: "Layout 31 (3/4, 1/4)", layout_type: "31", flex: ["w-3/4", "w-1/4"], images: 2 }, // Flex layout
	{
		name: "Layout One Large, Two Small",
		layout_type: "211",
		flex: ["w-1/2", "w-1/4", "w-1/4"],
		images: 3,
	},
])

/* const selectLayout = (layoutType: string) => {
	modelValue.value =
		layouts.value.find((layout) => layout.layout_type === layoutType) || null
} */

const onSubmitNote = async (layoutType: string) => {
	emit("update:modelValue", layoutType)
	isModalOpen.value = false
}
</script>

<template>
	<div class="w-full flex justify-center">
		<Button  @click="isModalOpen = true" label="Change Layout" icon="fas fa-th-large" />
	</div>

	<Modal :isOpen="isModalOpen" @onClose="() => (isModalOpen = false)">
		<div class="h-auto px-2 overflow-auto">
			<div class="text-xl font-semibold mb-2">Select a Layout Type</div>
			<div class="relative">
				<div class="grid grid-cols-3 gap-4">
					<div v-for="layout in layouts" :key="layout.layout_type"
						class="p-2 cursor-pointer transition-all group relative" :class="{
							'border-blue-500 ring-2 rounded': modelValue === layout.layout_type,
							'hover:border-blue-300 hover:bg-gray-100': !modelValue || modelValue !== layout.layout_type,
						}">
						<div class="border rounded-lg p-4">
							<div v-if="layout.grid" :class="'grid gap-2 ' + layout.grid">
								<div v-for="i in layout.images" :key="i"
									class="p-2 bg-gray-200 h-20 text-center border border-dashed rounded-lg">
									<font-awesome-icon :icon="['fas', 'image']"
										class="mx-auto h-12 w-12 text-gray-400" />
								</div>
							</div>

							<div v-else class="flex gap-2">
								<div v-for="(flexClass, index) in layout.flex" :key="index" :class="flexClass"
									class="p-2 bg-gray-200 h-20 text-center border border-dashed rounded-lg">
									<font-awesome-icon :icon="['fas', 'image']"
										class="mx-auto h-12 w-12 text-gray-400" />
								</div>
							</div>
						</div>
						<div
							class="absolute inset-0 flex items-center justify-center bg-opacity-50 bg-black text-white opacity-0 transition-opacity duration-300 group-hover:opacity-100">
							<Button label="Select"  @click="() => onSubmitNote(layout.layout_type)"
								class="transition-colors" :class="{
									'bg-gray-300': !modelValue,
									'bg-blue-500 hover:bg-blue-600': modelValue,
								}" />
						</div>
					</div>
				</div>
			</div>

			<div class="flex justify-end gap-x-2 mt-3">
				<Button label="Cancel" @click="() => (isModalOpen = false)" type="tertiary" />
				<!-- <Button
          label="Submit"
          :disabled="!modelValue"
          @click="onSubmitNote"
          class="transition-colors"
          :class="{
            'bg-gray-300': !modelValue,
            'bg-blue-500 hover:bg-blue-600': modelValue,
          }"
        /> -->
			</div>
		</div>
	</Modal>
</template>
