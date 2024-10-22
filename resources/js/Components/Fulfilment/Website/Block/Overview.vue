<script setup lang="ts">
import Splitter from 'primevue/splitter';
import SplitterPanel from 'primevue/splitterpanel';
import Editor from "@/Components/Forms/Fields/BubleTextEditor/EditorV2.vue"

const props = withDefaults(defineProps<{
    modelValue?: any
    isEditable?: boolean
}>(), {
    isEditable: true
})

const emits = defineEmits<{
    (e: 'autoSave'): void
}>()

console.log(props)
</script>

<template>
    <div class="relative  overflow-hidden">
        <Splitter style="{height: 300px}">
            <SplitterPanel class="flex items-center justify-center" :size="25" :minSize="10"> Panel 1 </SplitterPanel>
            <SplitterPanel class="flex items-center justify-center" :size="75">
                <div class="p-4">
                    <Editor :editable="isEditable" v-model="modelValue.text.value"
                        @update:modelValue="() => emits('autoSave')" />
                </div>
            </SplitterPanel>
        </Splitter>
    </div>
</template>
