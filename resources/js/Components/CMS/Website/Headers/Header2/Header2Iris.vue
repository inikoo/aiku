<script setup lang="ts">
import { getStyles } from "@/Composables/styles"
import { checkVisible } from "@/Composables/Workshop"
import { inject } from "vue"
import Image from "@/Components/Image.vue"

const props = defineProps<{
	fieldValue: {
		headerText: string
		chip_text: string
		container: {
			properties: Record<string, string>
		}
		logo: {
			properties: Record<string, string>
			alt: string
			image: {
				source: string
			}
		}
		text: {
			text: string
			visible: boolean | null
			container: {
				properties: Record<string, string>
			}
		}
	}
	loginMode: boolean
}>()

const isLoggedIn = inject("isPreviewLoggedIn", false)
</script>

<template>
	<div
		ref="_parentComponent"
		class="relative"
		:style="getStyles(fieldValue.container.properties)">
		<div class="flex flex-col justify-between items-center py-4 px-6">
			<div class="w-full grid grid-cols-3 items-center gap-6">
				<!-- Logo -->
				<component
					:is="fieldValue?.logo?.url ? 'a' : 'div'"
					:href="fieldValue?.logo?.url || '#'"
				>
				    <Image 
                        :style="getStyles(fieldValue.logo.properties)"
                        :alt="fieldValue?.logo?.alt" 
                         :imageCover="true"
                        :src="fieldValue?.logo?.image?.source" 
                        :imgAttributes="fieldValue?.logo.image?.attributes"
                       >
                    </Image>
				</component>

				<!-- Search Bar -->
				<div class="relative justify-self-center w-full max-w-md">
					<!-- Search bar can be added here if needed -->
				</div>

				<div
					:style="{
						width: fieldValue.text?.container?.properties?.width
							? `${fieldValue.text?.container?.properties?.width}`
							: 'auto',
						height: fieldValue.text?.container?.properties?.height
							? `${fieldValue.text?.container?.properties?.height}`
							: 'auto',
						top: fieldValue.text?.container?.properties?.position?.top
							? `${fieldValue.text?.container?.properties?.position?.top}`
							: 'auto',
						right: fieldValue.text?.container?.properties?.position?.right
							? `${fieldValue.text?.container?.properties?.position?.right}`
							: '0px',
                        left: fieldValue.text?.container?.properties?.position?.left
                        ? `${fieldValue.text?.container?.properties?.position?.left}`
                        : '0px',
                        bottom: fieldValue.text?.container?.properties?.position?.bottom
                        ? `${fieldValue.text?.container?.properties?.position?.bottom}`
                        : '0px',
					}">
					<div v-html="fieldValue?.text?.text" />
				</div>
			</div>
		</div>
	</div>
</template>

<style scoped></style>
