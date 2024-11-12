<script setup lang="ts">
import { ref, watch } from 'vue';
import Button from '@/Components/Elements/Buttons/Button.vue';
import InputText from 'primevue/inputtext';
import { isArray } from 'lodash';

const props = defineProps<{
    modelValue: any[],
    background?:string
}>();

const emit = defineEmits(['update:modelValue']);

// Deep clone modelValue to keep a local reference
// const items = ref(isArray(props.modelValue) ? [...props.modelValue] : []);

// Watch for changes in the local items array and emit updates
// watch(items, (newVal) => {
//     emit('update:modelValue', newVal);
// }, { deep: true });

// watch(
//     () => props.modelValue,
//     (newValue) => {
//        console.log('inii')
//     },
//     { deep: true, immediate: true } // Immediate to apply initially, deep for nested changes
// );

// Function to add a new item
const addItem = () => {
    props.modelValue.push(''); // Add an empty string as a new item
};

// Function to remove an item by index
const removeItem = (index: number) => {
    props.modelValue.splice(index, 1);
};
</script>

<template>
    <div class="p-6 max-w-md mx-auto bg-white">
        
        <!-- Input fields with delete button -->
        <div v-for="(item, index) in modelValue" :key="index" class="flex items-center mb-3 space-x-2">
            <InputText 
                type="text" 
                v-model="modelValue[index]" 
                placeholder="Enter value" 
                class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
            />
            <Button 
                :icon="['far', 'fa-trash-alt']"
                @click="removeItem(index)" 
                :style="'red'"
            />
        </div>
        
        <!-- Add button -->
        <Button 
            label="Add Item" 
            icon="fa fa-plus" 
            @click="addItem" 
            full
        />
    </div>
</template>

<style scoped>
/* Optional: Custom styling for PrimeVue InputText */
.p-inputtext {
    background-color: #f9fafb; /* Light background */
    color: #374151; /* Text color */
}
</style>
