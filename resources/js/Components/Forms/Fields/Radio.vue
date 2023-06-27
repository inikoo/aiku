<template>
    <div>
        <!-- <label class="text-base font-semibold text-gray-800 capitalize">{{ fieldName }}</label> -->
        <!-- <p class="text-xs text-gray-500 capitalize italic">{{ form[fieldName] }}</p> -->
        <fieldset class="select-none">
            <legend class="sr-only"></legend>
            <div class="space-y-2">
                <div v-for="(option, index) in fieldData.options" :key="option.label + index" class="flex items-center">
                    <input v-model="form[fieldName]" :id="option.label + index" :key="option.label + index" :name="option.value" type="radio" :value="option.value" :checked="option.value == form[fieldName]"
                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-0 focus:outline-none focus:ring-transparent cursor-pointer" />
                    <label :for="option.label + index" class="ml-3 block text-sm font-medium leading-6 text-gray-600 cursor-pointer">
                        {{ option.label }}
                    </label>
                </div>
            </div>
        </fieldset>
    </div>
</template>
  
<script setup>
import { ref } from 'vue'
const props = defineProps(['form', 'fieldName', 'fieldData'])

</script>