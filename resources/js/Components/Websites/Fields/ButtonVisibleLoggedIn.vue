<script setup lang="ts">
import { ref, watch, defineEmits } from 'vue';
import { trans } from 'laravel-vue-i18n';
import SelectButton from 'primevue/selectbutton';

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
    { label: 'All', value: 'all' },
    { label: 'Logged In', value: 'login' },
    { label: 'Logged Out', value: 'logout' },
]);

// Local ref for visibility value
const visible = ref(props.modelValue.visible ?  options.value.find((item)=>item.value == props.modelValue.visible)   : 'all');

// Watch for changes in the local visible value and emit updates
watch(visible, (newValue) => {
    console.log(newValue)
    emit('update:modelValue', { ...props.modelValue, visible: newValue.value });
});



</script>



<template>
    <div class="border-b pb-3 border-gray-300 mb-5">
        <div class="my-2 text-gray-500 text-xs font-semibold">{{ trans('Visible') }}</div>
        <SelectButton v-model="visible" :options="options" optionLabel="label">
            <template #option="slotProps">
                <span class="text-xs">{{ slotProps.option.label }}</span>
            </template>
        </SelectButton>
    </div>
</template>




<style lang="scss" scoped></style>
