<script setup lang="ts">
import { ref, watch, computed, onBeforeMount } from 'vue';
import DimensionProperty from '@/Components/Workshop/Properties/DimensionProperty.vue';
import { trans } from 'laravel-vue-i18n';

// Define props and emits
const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['update:modelValue']);

// Local copy of the model for two-way binding
const localModel = ref({
    height: { value: null, unit: 'px' },
    width: { value: null, unit: '%' },
});

// Sync with the prop value initially
onBeforeMount(() => {
    if (props.modelValue) {
        localModel.value = { ...props.modelValue };
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

const compModel = computed(() => JSON.stringify(localModel.value));
</script>

<template>
    <div v-if="localModel" class="border-t border-gray-300 bg-gray-100 pb-3">
        <div class="w-full text-center py-1 font-semibold select-none">{{ trans('Dimension') }}</div>
        <DimensionProperty v-model="localModel" />
    </div>
</template>

<style scoped></style>
