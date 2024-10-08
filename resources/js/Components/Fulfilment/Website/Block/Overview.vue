<script setup lang="ts">

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
    <div class="relative box-border overflow-hidden">
        <div v-html="modelValue?.value"></div>
    </div>
</template>
