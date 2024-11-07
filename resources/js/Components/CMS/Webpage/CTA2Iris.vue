<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faCube, faLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref } from "vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Editor from "@/Components/Forms/Fields/BubleTextEditor/EditorV2.vue"
import { getStyles } from "@/Composables/styles"

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
	(e: "update:modelValue", value: string | number): void
	(e: "autoSave"): void
}>()
</script>

<template>
	<div :style="getStyles(modelValue.container.properties)">
		<div class="relative isolate overflow-hidden px-6 py-24 text-center shadow-2xl l sm:px-16">
			<span class="text-xl font-semibold w-fit leading-6">
				<div v-html="modelValue.headline" />
			</span>
		
			<!--   <h2 class="mx-auto max-w-2xl text-3xl font-bold tracking-tight text-white sm:text-4xl">Boost your productivity today.</h2> -->
		
			<span class="text-xl font-semibold w-fit leading-6">
				<div v-html="modelValue.description" />
			</span>
		
			<div class="mt-10 flex items-center justify-center gap-x-6">
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
