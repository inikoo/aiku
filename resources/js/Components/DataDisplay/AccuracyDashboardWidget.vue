<script setup lang="ts">
import { ref } from "vue";
import Knob from 'primevue/knob';

// Props for initializing accuracy
const props = defineProps({
    initialAccuracy: {
        type: Number,
        default: 50,
    },
});

// Accuracy state
const accuracy = ref(props.initialAccuracy);
</script>

<template>
    <div class="bg-white text-gray-900 p-4 rounded-lg flex flex-col items-center w-48 shadow-lg">
        <h3 class="text-sm font-bold mb-2 text-gray-600">Inventory Accuracy</h3>
        
        <!-- PrimeVue Knob -->
        <Knob 
            v-model="accuracy" 
            size="120" 
            valueColor="#00C853" 
            rangeColor="#E0E0E0"
            showValue
            valueTemplate="{value}%" 
            :step="0.1"
            :min="0" 
            :max="100" 
            :strokeWidth="12"
        />
        
        <!-- Label below knob for large percentage -->
        <div class="text-lg font-bold mt-2 text-gray-900">
            {{ accuracy.toFixed(1) }}%
        </div>
    </div>
</template>

<style scoped>
/* Styling adjustments for white background */
.bg-white {
    background-color: #ffffff;
}

.text-gray-600 {
    color: #6b7280; /* Light gray for headings */
}

.text-gray-900 {
    color: #1f2937; /* Dark gray for text */
}

.shadow-lg {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
</style>
