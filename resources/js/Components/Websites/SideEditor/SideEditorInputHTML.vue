<script setup lang='ts'>
import PureEditor from '@/Components/Pure/PureEditor.vue'
const props = defineProps<{
    
}>()

const model = defineModel<{ text: string }>()

</script>

<template>
    <PureEditor v-model="model" />
</template>