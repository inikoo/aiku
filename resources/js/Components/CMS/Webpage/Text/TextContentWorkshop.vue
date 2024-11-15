<script setup lang="ts">
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"
import EditorV2 from "@/Components/Forms/Fields/BubleTextEditor/EditorV2.vue";
import { getStyles } from '@/Composables/styles'
import { watch, ref } from "vue";

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

const editable = ref(props.isEditable)

watch(()=>props.isEditable,(value)=>{
    editable.value = value
})


</script>

<template>
    <div id="blockTextContent" :style="getStyles(modelValue?.container.properties)">
        <EditorV2 
            v-model="modelValue.value" 
            :editable="isEditable" 
            :key="editable"
            @update:modelValue="() => emits('autoSave')"
        />
    </div>
</template>