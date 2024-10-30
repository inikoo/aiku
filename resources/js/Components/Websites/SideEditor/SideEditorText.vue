<script setup lang='ts'>
import InputText from 'primevue/inputtext'
const props = defineProps<{
    
}>()

const model = defineModel<{ text: string }>()
</script>

<template>
    <div class="p-2">
        <InputText v-model="model" />
    </div>
</template>