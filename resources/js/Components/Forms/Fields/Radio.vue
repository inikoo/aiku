<template>
    <div>
        <!-- <label class="text-base font-semibold text-gray-800 capitalize">{{ fieldName }}</label> -->
        <!-- <p class="text-xs text-gray-500 capitalize italic">{{ form[fieldName] }}</p> -->
        <fieldset class="mt-4 select-none">
            <legend class="sr-only"></legend>
            <div class="space-y-2">
                <div v-for="(option, index) in fieldData.options" :key="index" class="flex items-center">
                    <input v-model="form[fieldName]" :id="option.label + index" name="notification-method" type="radio" :value="option.value" :checked="option.value == form[fieldName]"
                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600 cursor-pointer" />
                    <label :for="option.label + index" class="ml-3 block text-sm font-medium leading-6 text-gray-800 cursor-pointer">
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