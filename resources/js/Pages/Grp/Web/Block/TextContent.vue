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
        </div>

        <div class="p-4  overflow-y-auto outline-none prose max-w-none">
            <div :class="!editMode ? '' : 'hidden'" v-html="modelValue.value" />
        </div>

    </div>
</template>


<style lang="scss">
/* Basic editor styles */

blockquote {
    padding-left: 1rem !important;
    border-left: 3px solid rgba(#0D0D0D, 0.1) !important;
}

ul,
ol {
    padding: 0 1rem !important;
}

ul {
    list-style: disc !important
}

ol {
    list-style: decimal !important
}

p:empty::after {
    content: "\00A0";
}

</style>