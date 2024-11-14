<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faCube, faLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref } from "vue"
import { getStyles } from "@/Composables/styles"

library.add(faCube, faLink)

const props = defineProps<{
	modelValue: {
		headline: String,
		button: {
			text: String
			container: {
				properties: object
			}
		}
	},
	container: {
		properties: Object
	}
}>()

</script>

<template>
	<div :style="getStyles(modelValue.container.properties)">
		<div class="relative isolate overflow-hidden px-6 py-24 text-center shadow-2xl l sm:px-16">
			<section v-html="modelValue.headline" />

			<div class="mt-10 flex items-center justify-center gap-x-6">
				<div typeof="button" :style="getStyles(modelValue.button.container.properties)"
					class="mt-10 flex items-center justify-center w-64 mx-auto gap-x-6">
					{{ modelValue.button.text }}
				</div>
			</div>
		</div>
	</div>
</template>
