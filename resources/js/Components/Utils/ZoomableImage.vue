<!--
    * Author: Vika Aqordi
    * Created on: 2026-10-16 10:08
    * Github: https://github.com/aqordeon
    * Copyright: 2026
-->

<script setup lang="ts">
import { ref } from "vue"
import Image from "@common/Components/Image.vue"
import ProductImageViewerDialog from "@/Components/Product/ProductImageViewerDialog.vue"
import { ctrans } from "@/Composables/useTrans"

const props = defineProps<{
	src: object | null
	full?: { original_url: string; download_url: string } | null
	alt?: string | null
}>()

const isViewerVisible = ref(false)

function openViewer() {
	if (props.full) {
		isViewerVisible.value = true
	}
}
</script>

<template>
	<div
		class="flex-none overflow-hidden rounded border border-gray-100"
		:class="{ 'cursor-zoom-in': full }"
		v-tooltip="full ? ctrans('Click to enlarge and download in full resolution') : undefined"
		@click="openViewer"
	>
		<Image :src="src" :alt="alt ?? undefined" imageCover class="h-full w-full" />
		<ProductImageViewerDialog
			v-if="isViewerVisible && full"
			v-model:visible="isViewerVisible"
			:images="[{ source: src, alt: alt ?? undefined, original_url: full.original_url, download_url: full.download_url }]"
		/>
	</div>
</template>
