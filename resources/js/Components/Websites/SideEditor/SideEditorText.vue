<script setup lang='ts'>
import InputText from 'primevue/inputtext'
const props = defineProps<{
    
}>()

const model = defineModel<{ text: string }>()

</script>

<template>
    <InputText v-model="model.text" />
</template>