<script setup lang="ts">
import { getStyles } from "@/Composables/styles"
import Image from "@/Components/Image.vue"
import Editor from "@/Components/Forms/Fields/BubleTextEditor/EditorV2.vue"

const props = defineProps<{
	modelValue: any
	webpageData?: any
	blockData?: Object
}>()

const emits = defineEmits<{
	(e: "update:modelValue", value: string | number): void
	(e: "autoSave"): void
}>()
</script>

<template>
	<div class="container mx-auto flex flex-wrap lg:flex-nowrap gap-8 p-6">
		<!-- Left Column: Image with Badge -->
		<div class="relative w-full lg:w-1/2">
			<!-- Image -->
			<img
				src="https://web.moxcreative.com/markeezo/wp-content/uploads/sites/18/2023/03/executive-managers-analyzing-marketing-statistics.jpg"
				alt="Executive managers analyzing marketing statistics"
				class="w-full h-auto rounded-lg shadow-lg" />
		<!-- 	<div
				class="absolute top-4 left-4 bg-orange-100 text-center p-4 rounded-full shadow-md animate-bounce">
				<h2 class="text-3xl font-bold text-orange-600">25+</h2>
				<p class="text-sm text-gray-700">Years of Experience</p>
			</div> -->
		</div>

		<div class="w-full lg:w-1/2 space-y-6 flex flex-col justify-center">
			<h6 class="text-lg text-gray-500 uppercase">Who we are</h6>
			<h2 class="text-4xl font-extrabold text-blue-800">
				Real people delivering real results.
			</h2>

			<p class="text-gray-600 text-lg">
				Pharetra ornare consequat ad ligula facilisis habitasse pretium sollicitudin
				natoque. Ornare parturient hendrerit vulputate mi potenti per justo. Commodo eros
				amet ipsum himenaeos tincidunt.
			</p>

			<div class="space-y-6">
				<div class="flex items-start gap-4">
					<div class="bg-orange-500 text-white p-3 rounded-full">
						<i class="icon icon-chart-bars text-xl"></i>
					</div>
					<div>
						<h4 class="text-lg font-semibold text-gray-800">Marketing Agency</h4>
						<p class="text-sm text-gray-600">
							Ligula mattis mollis felis euismod diam conubia facilisi quam efficitur
							senectus luctus.
						</p>
					</div>
				</div>
				<div class="flex items-start gap-4">
					<div class="bg-orange-500 text-white p-3 rounded-full">
						<i class="icon icon-rocket text-xl"></i>
					</div>
					<div>
						<h4 class="text-lg font-semibold text-gray-800">SEO Services</h4>
						<p class="text-sm text-gray-600">
							Ligula mattis mollis felis euismod diam conubia facilisi quam efficitur
							senectus luctus.
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
