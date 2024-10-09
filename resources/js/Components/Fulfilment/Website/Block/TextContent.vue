<script setup lang="ts">
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"
import { getStyles } from '@/Composables/styles'

const props = withDefaults(defineProps<{
    modelValue?: {
        value: string
    }
    isEditable?: boolean
    properties: {}
}>(), {
    isEditable: true
})

const emits = defineEmits<{
    (e: 'autoSave'): void
}>()

</script>

<template>
    <div class="relative box-border overflow-hidden" id="blockTextContent" :style="getStyles(properties)">
        <Editor
            v-if="isEditable"
            v-model="modelValue.value"
            @update:modelValue="() => emits('autoSave')"
        />
        <div v-else v-html="modelValue?.value"></div>
    </div>
</template>
<style lang="scss" scoped>


#blockTextContent blockquote {
    padding-left: 1rem;
    border-left: 3px solid rgba(#0D0D0D, 0.1);
}


#blockTextContent ul,
#blockTextContent ol {
    padding: 0 1rem;
}

#blockTextContent ul {
    list-style: disc
}

#blockTextContent ol {
    list-style: decimal
}

#blockTextContent h1 {
    display: block;
    font-size: 2em;
    margin-block-start: 0.67em;
    margin-block-end: 0.67em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    font-weight: bold;
    unicode-bidi: isolate;
}

#blockTextContent h2 {
    display: block;
    font-size: 1.5em;
    margin-block-start: 0.83em;
    margin-block-end: 0.83em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    font-weight: bold;
    unicode-bidi: isolate;
}

#blockTextContent h3 {
    display: block;
    font-size: 1.17em;
    margin-block-start: 1em;
    margin-block-end: 1em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    font-weight: bold;
    unicode-bidi: isolate;
}

#blockTextContent p:empty::after {
    content: "\00A0";
}
</style>