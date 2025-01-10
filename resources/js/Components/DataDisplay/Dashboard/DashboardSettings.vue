<script setup lang="ts">
const props = defineProps<{
	dashboard: Object
	intervalOptions: Object
	selectedDateOption: String
	isOrganisation: Boolean
}>()
console.log(props);
const emit = defineEmits(["toggle-currency", "update-interval"])
</script>

<template>
	
</template>
