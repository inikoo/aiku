<script setup lang="ts">
import { watch, onMounted, ref } from 'vue'
import PaddingMarginProperty from '@/Components/Workshop/Properties/PaddingMarginProperty.vue'
import { trans } from 'laravel-vue-i18n'

// Define props and emits
const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['update:modelValue']);

// Create a local copy of the model for internal use
const localModel = ref({
    unit: "px",
    top: {
        value: null
    },
    left: {
        value: null
    },
    right: {
        value: null
    },
    bottom: {
        value: null
    }
});

// Initialize localModel with the prop value on mount
onMounted(() => {
    if (props.modelValue) {
        localModel.value = props.modelValue;
    }
});

// Watch localModel and emit updates
watch(
    localModel,
    (newValue) => {
        emit('update:modelValue', newValue);
    },
    { deep: true }
);

</script>

<template>
    <div v-if="localModel" class="border-t border-gray-300 bg-gray-100 pb-3">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Padding') }}</div>
        <PaddingMarginProperty v-model="localModel" />
    </div>
</template>

<style scoped></style>
