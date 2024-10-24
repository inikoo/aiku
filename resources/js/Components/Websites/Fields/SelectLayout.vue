<script setup lang='ts'>
import { ref } from 'vue';
import Select from 'primevue/select';

const model = defineModel<{ name: string }>()
const layout = ref([
    { name: 'Layout Single Wide Image', layout_type: '1' },
    { name: 'Layout Two Equal Images', layout_type: '2' },
    { name: 'Layout Three Equal Images', layout_type: '3' },
    { name: 'Layout Four Equal Images', layout_type: '4' },
    { name: 'Layout One Large, Two Small', layout_type: '12' },
    { name: 'Layout Two Large, One Small', layout_type: '21' },
    { name: 'Layout One Small, One Large', layout_type: '13' },
    { name: 'Layout One Large, One Small', layout_type: '31' },
    { name: 'Layout One Large, Two Small', layout_type: '211' }
]);
console.log(model,'dad');

</script>

<template>
  <div class="card flex justify-center">
    <Select 
      v-model="model"
      :options="layout" 
      optionLabel="name"  
      class="w-full md:w-56" 
      placeholder="Select a Layout"
    />
  </div>

  
</template>
