<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import BorderProperty from '@/Components/Workshop/Properties/BorderProperty.vue';
import { trans } from 'laravel-vue-i18n';

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
    top: { value: 0 },
    left: { value: 0 },
    unit: 'px',
    color: null,
    right: { value: 0 },
    bottom: { value: 0 },
    rounded: {
        unit: 'px',
        topleft: { value: 0 },
        topright: { value: 0 },
        bottomleft: { value: 0 },
        bottomright: { value: 0 },
    },
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
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Border') }}</div>
        <BorderProperty v-model="localModel" />
    </div>
</template>

<style scoped></style>
