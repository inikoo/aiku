<script setup lang="ts">
import EditorV2 from "@/Components/Forms/Fields/BubleTextEditor/EditorV2.vue"

withDefaults(defineProps<{
    disabled?: boolean
    placeholder?: string
    isError?: boolean
}>(), {
    disabled: false,
})

const model = defineModel<string>({ default: "" })

const toggle = ["bold", "italic", "underline", "bulletList", "orderedList", "customLink", "undo", "redo", "clear"]
</script>

<template>
    <div
        class="min-h-20 rounded-md bg-white px-3 py-2 text-sm ring-1 focus-within:ring-2"
        :class="[
            isError ? 'ring-red-400 focus-within:ring-red-500' : 'ring-gray-300 focus-within:ring-[--app-accent]',
            disabled ? 'bg-gray-100 text-gray-500' : '',
        ]"
    >
        <EditorV2 v-model="model" :editable="!disabled" :placeholder="placeholder" :toggle="toggle" />
    </div>
</template>
