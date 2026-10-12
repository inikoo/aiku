<script setup lang="ts">
import { FontAwesomeIcon, FontAwesomeLayers } from "@fortawesome/vue-fontawesome"
import { faClock, faPencil } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"

library.add(faClock, faPencil)

defineProps<{
	layers: (string | string[])[]
}>()
</script>

<template>
	<FontAwesomeLayers class="inline-block shrink-0 h-[1.25em] w-[1.25em] align-[-0.2em]" aria-hidden="true">
		<FontAwesomeIcon :icon="layers[0]" transform="shrink-2 left-2 up-1" />
		<FontAwesomeIcon :icon="layers[1]" transform="shrink-8 right-5 down-5" />
	</FontAwesomeLayers>
</template>
