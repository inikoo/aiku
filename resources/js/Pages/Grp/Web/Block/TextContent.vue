<script setup lang="ts">
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"
import { ref, computed } from 'vue'

const props = defineProps<{
    modelValue: any
}>()

const editMode = ref(false)

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
    (e: 'autoSave'): void
}>()

const parsedHtml = computed(() => {
    const parser = new DOMParser();
    const elem = parser.parseFromString(modelValue.value, 'text/html');
    return elem.body.innerText;
});

</script>

<template>
    <div class="relative">
        <div>
            <Editor v-model="modelValue.value"  @update:modelValue="()=>emits('autoSave')"/>
        </div>
    </div>
</template>


<style lang="scss">
/* Basic editor styles */

blockquote {
    padding-left: 1rem;
    border-left: 3px solid rgba(#0D0D0D, 0.1);
}


ul,
ol {
    padding: 0 1rem;
}

ul {
    list-style: disc
}

ol {
    list-style: decimal
}

h1 {
    display: block;
    font-size: 2em;
    margin-block-start: 0.67em;
    margin-block-end: 0.67em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    font-weight: bold;
    unicode-bidi: isolate;
}

h2 {
    display: block;
    font-size: 1.5em;
    margin-block-start: 0.83em;
    margin-block-end: 0.83em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    font-weight: bold;
    unicode-bidi: isolate;
}

h3 {
    display: block;
    font-size: 1.17em;
    margin-block-start: 1em;
    margin-block-end: 1em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    font-weight: bold;
    unicode-bidi: isolate;
}

p:empty::after {
    content: "\00A0";
}
</style>