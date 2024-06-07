<script setup lang="ts">
import TextEditorBuble from "@/Components/Forms/Fields/BubleTextEditor/BubleTextEditor.vue"
import TextEditor from "@/Components/Forms/Fields/TextEditor.vue"
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"
import { ref, computed } from 'vue'
import Button from "@/Components/Elements/Buttons/Button.vue";

const props = defineProps<{
    modelValue: Object
}>()

console.log(props)
const editMode = ref(false)

const parsedHtml = computed(() => {
    const parser = new DOMParser();
    const elem = parser.parseFromString(modelValue.value, 'text/html');
    return elem.body.innerText;
});

</script>

<template>
    <div class="relative">
        <div class="absolute top-2 right-2 flex space-x-2">
            <Button v-if="!editMode" @click="editMode = true" :icon="['far', 'fa-pencil']" size="xs" />
            <Button v-else :icon="['far', 'times']" size="xs" @click="editMode = false" />
        </div>

        <div :class="editMode ? '' : 'hidden'">
            <Editor v-model="modelValue.value">
            </Editor>
            <!--             <TextEditor field-name="value" :form="modelValue"/>
 -->
        </div>

        <div class="p-4  overflow-y-auto outline-none prose max-w-none">
            <div :class="!editMode ? '' : 'hidden'" v-html="modelValue.value" />
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