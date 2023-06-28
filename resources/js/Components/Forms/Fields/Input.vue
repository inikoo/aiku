<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 14 Mar 2023 23:44:10 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->


<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faExclamationCircle, faCheckCircle } from "@/../private/pro-solid-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faExclamationCircle, faCheckCircle);

const props = defineProps<{
    form: any,
    fieldName: string,
    options?: any,
    fieldData?: {
        placeholder: string
    }
}>()

</script>
<template>
    <div class="relative">
        <div class="relative">
            <input :type="props.options?.type ?? 'text'" v-model.trim="form[fieldName]" @input="form.errors[fieldName] = ''"
                :placeholder="fieldData.placeholder" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" />
            <div v-if="form.errors[fieldName] || form.recentlySuccessful"
                class="absolute inset-y-2/4 right-0 pr-3 flex items-center pointer-events-none">
                <FontAwesomeIcon icon="fas fa-exclamation-circle" v-if="form.errors[fieldName]" class="h-5 w-5 text-red-500"
                    aria-hidden="true" />
                <FontAwesomeIcon icon="fas fa-check-circle" v-if="form.recentlySuccessful"
                    class="mt-1.5  h-5 w-5 text-green-500" aria-hidden="true" />
            </div>
        </div>

        <!-- Counter: Letters and Words -->
        <div v-if="props.options?.counter" class="grid grid-flow-col text-xs italic text-gray-500 mt-2 space-x-12 justify-start">
            <p class="">
                Letters: {{ form[fieldName].length }}
            </p>
            <p class="">
                Words: {{ form[fieldName].trim().split(/\s+/).filter(Boolean).length }}
            </p>
        </div>
    </div>
    <p v-if="form.errors[fieldName]" class="mt-2 text-sm text-red-600" :id="`${fieldName}-error`">{{ form.errors[fieldName] }}</p>
</template>


