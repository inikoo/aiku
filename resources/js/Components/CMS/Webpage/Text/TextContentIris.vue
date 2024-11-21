<script setup lang="ts">
import { getStyles } from '@/Composables/styles'

const props = defineProps<{
	modelValue: any
	webpageData: any
	blockData: Object
}>()

</script>

<template>
    <div id="blockTextContent" :style="getStyles(modelValue?.container.properties)">
        <div class="editor-class" v-html="modelValue.value"></div>
    </div>
</template>
