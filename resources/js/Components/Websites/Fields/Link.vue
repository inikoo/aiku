<script setup lang="ts">
import { ref, watch, defineEmits } from 'vue'
import { trans } from 'laravel-vue-i18n'
import SelectButton from 'primevue/selectbutton'
import PureInput from '@/Components/Pure/PureInput.vue';

// Define props
const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    }
});

// Emits to notify parent component of changes
const emit = defineEmits(['update:modelValue']);
// Options for the SelectButton
const options = ref([
    { label: 'Internal', value: 'internal' },
    { label: 'External', value: 'external' },
]);

// Local ref for visibility value
const visible = ref('internal');

// Watch for changes in the local visible value and emit updates
watch(visible, (newValue) => {
    emit('update:modelValue', { ...props.modelValue, visible: newValue.value });
});
</script>

<template>
    <div class="border-b pb-3 border-gray-300 mb-5">
        <SelectButton v-model="visible" :options="options" optionLabel="label" optionValue="value">
            <template #option="slotProps">
                <span class="text-xs">{{ slotProps.option.label }}</span>
            </template>
        </SelectButton>
    </div>
    <div class="my-2 text-gray-500 text-xs font-semibold">{{ trans('Link') }}</div>
    <PureInput v-model="visible"/>
</template>

<style lang="scss" scoped>
</style>
