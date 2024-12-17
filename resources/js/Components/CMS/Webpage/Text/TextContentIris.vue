<script setup lang="ts">
import { getStyles } from '@/Composables/styles'

const props = defineProps<{
	fieldValue: {
        value: string
    }
    properties: {}
}>()

</script>

<template>
    <div id="blockTextContent" :style="getStyles(fieldValue?.container?.properties)">
        <div class="editor-class" v-html="fieldValue?.value"></div>
    </div>
</template>
