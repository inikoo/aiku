<template>
    <div>
        <!-- <label class="text-base font-semibold text-gray-800 capitalize">{{ fieldName }}</label> -->
        <!-- <p class="text-xs text-gray-500 capitalize italic">{{ form[fieldName] }}</p> -->
        <fieldset class="select-none">
            <legend class="sr-only"></legend>
            <div class="flex items-center gap-x-8 gap-y-1 flex-wrap ">
                <!-- Mode Radio: Normal -->
                <div v-if="fieldData.mode === 'normal'" v-for="(option, index) in fieldData.options"
                    :key="option.label + index" class="inline-flex gap-x-2.5 items-center">
                    <input v-model="form[fieldName]" :id="option.label + index" :key="option.label + index"
                        :name="option.value" type="radio" :value="option.value" :checked="option.value == form[fieldName]"
                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-0 focus:outline-none focus:ring-transparent cursor-pointer" />
                    <label :for="option.label + index" class="flex items-center gap-x-1.5 cursor-pointer">
                        <p class="text-sm font-medium leading-6 text-gray-700 capitalize">
                            {{ option.value }}
                        </p>
                        <span v-if="option.label" class="font-light text-sm text-gray-400 capitalize">
                            {{ option.label }}
                            <!-- d -->
                        </span>
                    </label>
                </div>
                <div v-else-if="fieldData.mode === 'compact'">
                    <RadioGroup v-model="form[fieldName]" class="mt-2">
                        <RadioGroupLabel class="sr-only">Choose a memory option</RadioGroupLabel>
                        <div class="flex gap-x-1.5 gap-y-1 flex-wrap">
                            <RadioGroupOption as="template" v-for="(option, index) in fieldData.options" :key="option.value"
                                :value="option" v-slot="{ active, checked }">
                                <div
                                    :class="[
                                        'cursor-pointer focus:outline-none flex items-center justify-center rounded-md py-3 px-3 text-sm font-medium capitalize', 
                                        active ? 'ring-2 ring-indigo-600 ring-offset-2' : '',
                                        checked ? 'bg-indigo-600 text-white hover:bg-indigo-500' : 'ring-1 ring-inset ring-gray-300 bg-white text-gray-700 hover:bg-gray-50',
                                    ]">
                                    <RadioGroupLabel as="span">{{ option.value }}</RadioGroupLabel>
                                </div>
                            </RadioGroupOption>
                        </div>
                    </RadioGroup>
                </div>
            </div>
        </fieldset>
    </div>
</template>
  
<script setup lang="ts">
import { ref } from 'vue'
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue'
const props = defineProps(['form', 'fieldName', 'fieldData'])

</script>