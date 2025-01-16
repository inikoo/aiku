<script setup lang="ts">
const props = withDefaults(
	defineProps<{
		showRedBorder: boolean
		widgetData: {
			value: string
			description: string
			status: "success" | "warning" | "danger" | "information" | "neutral"
			type?: "number" | "currency"
			currency_code?: string
		}
		visual?: {
			type: string
			label: any
			value: any
		}
	}>(),
	{
		widgetData: () => {
			return {
				value: "0",
				description: "",
				status: "information",
			}
		},
	}
)
</script>

<template>
    
</template>