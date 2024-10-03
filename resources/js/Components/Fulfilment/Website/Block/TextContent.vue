<script setup lang="ts">
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"

const props = withDefaults(defineProps<{
    modelValue?: {
        value: string
    }
    isEditable?: boolean
    properties: {

    }
}>(), {
    isEditable: true
})

const emits = defineEmits<{
    (e: 'autoSave'): void
}>()

</script>

<template>
    <div class="relative box-border" id="blockTextContent"
        :style="{
            paddingTop: (properties.padding.top.value || 0) + properties.padding.unit, 
            paddingBottom: (properties.padding.bottom.value || 0) + properties.padding.unit, 
            paddingRight: (properties.padding.right.value || 0) + properties.padding.unit, 
            paddingLeft: (properties.padding.left.value || 0) + properties.padding.unit,
            marginTop: (properties.margin.top.value || 0) + properties.margin.unit, 
            marginBottom: (properties.margin.bottom.value || 0) + properties.margin.unit, 
            marginRight: (properties.margin.right.value || 0) + properties.margin.unit, 
            marginLeft: (properties.margin.left.value || 0) + properties.margin.unit,
            background: properties.background.type === 'color' ? properties.background.color : properties.background.image,
            borderTop: `${properties.border.top.value}${properties.border.unit} solid ${properties.border.color}`,
            borderBottom: `${properties.border.bottom.value}${properties.border.unit} solid ${properties.border.color}`,
            borderRight: `${properties.border.right.value}${properties.border.unit} solid ${properties.border.color}`,
            borderLeft: `${properties.border.left.value}${properties.border.unit} solid ${properties.border.color}`,
        }"
    >
        <Editor
            v-if="isEditable"
            v-model="modelValue.value"
            @update:modelValue="() => emits('autoSave')"
        />
        <div v-else v-html="modelValue?.value"
            
        ></div>
    </div>
</template>


<style lang="scss">
/* Basic editor styles */

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