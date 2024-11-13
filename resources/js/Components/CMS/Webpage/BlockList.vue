<script setup lang="ts">
import { ref, onMounted } from "vue"
import {
	faPresentation,
	faCube,
	faText,
	faImage,
	faImages,
	faPaperclip,
	faShoppingBasket,
	faStar,
	faHandHoldingBox,
	faBoxFull,
	faBars,
	faBorderAll,
	faLocationArrow,
} from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { trans } from "laravel-vue-i18n"

import { Root, Daum } from "@/types/webBlockTypes"
import Image from "@/Components/Image.vue"

library.add(
	faPresentation,
	faCube,
	faText,
	faImage,
	faImages,
	faPaperclip,
	faShoppingBasket,
	faStar,
	faHandHoldingBox,
	faBoxFull,
	faBars,
	faBorderAll,
	faLocationArrow
)
const props = withDefaults(
	defineProps<{
		onPickBlock: Function
		webBlockTypes: Root
		scope?: string /* all|website|webpage */
	}>(),
	{
		scope: "all",
	}
)

const data = ref<Daum[]>([])

// Define active item state
const active = ref<Daum>(props.webBlockTypes.data[0])

// Filter webBlockTypes based on scope and save in data
onMounted(() => {
	if (props.scope === "all") {
		data.value = props.webBlockTypes.data
	} else {
		// Filter based on scope (e.g., 'website', 'webpage', etc.)
		data.value = props.webBlockTypes.data.filter((item) => item.scope === props.scope)
	}

	active.value = data.value[0] || null
})

</script>

<template>
	<div class="overflow-y-auto h-full select-none p-4 bg-gray-100">
		<div class="flex flex-wrap justify-center gap-4">
			<div
				v-for="block in data"
				:key="block.id"
				class="relative min-h-20 h-32 w-48 border rounded-lg shadow-lg cursor-pointer transition-transform transform hover:scale-105 hover:shadow-xl bg-white overflow-hidden"
				@click="onPickBlock(block)"
				:class="'border-gray-200'">
				<div class="h-3/4 w-full flex items-center justify-center rounded-t-lg bg-gray-50">
					<Image
						:src="block.screenshot"
						class="max-h-full max-w-full object-contain"
						alt="Screenshot of {{ block.name }}" />
				</div>

				<div
					class="absolute bottom-0 w-full h-1/4 bg-gradient-to-t from-black/70 via-black/40 to-transparent rounded-b-lg flex items-end text-white text-sm p-2 truncate">
					{{ block.name }}
				</div>
			</div>
		</div>
	</div>
</template>
