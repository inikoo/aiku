<template>
    <div class="">
        <div class="relative">
            <Multiselect :class="{ 'pr-8': form.errors[fieldName] || form.recentlySuccessful }" v-model="form[fieldName]"
                :options="props.options" :placeholder="multiselectPlaceholder" :searchable="true" :canClear="!props.required" :canDeselect="!props.required" required="true" />
            <div v-if="form.errors[fieldName] || form.recentlySuccessful"
                class="absolute inset-y-2/4 right-0 pr-3 flex items-center pointer-events-none bg-red-500">
                <FontAwesomeIcon icon="fas fa-exclamation-circle" v-if="form.errors[fieldName]" class="h-5 w-5 text-red-500"
                    aria-hidden="true" />
                <FontAwesomeIcon icon="fas fa-check-circle" v-if="form.recentlySuccessful"
                    class="mt-1.5  h-5 w-5 text-green-500" aria-hidden="true" />
            </div>
        </div>
        <p v-if="form.errors[fieldName]" class="mt-2 text-sm text-red-600" id="email-error">{{ form.errors[fieldName] }}</p>
    </div>
</template>
  
<script setup lang="ts">
import Multiselect from '@vueform/multiselect'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faExclamationCircle, faCheckCircle } from "../../../../private/pro-solid-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faExclamationCircle, faCheckCircle);

const props = defineProps<{ 'form', 'fieldName', 'options', required?: boolean }>()
const multiselectPlaceholder = "Select your country"

// Note:
// Value of the country (form[fieldName]) is a number
// The value of option Bangladesh (BD) is 1
</script>
  
<style src="@vueform/multiselect/themes/default.css"></style>
  