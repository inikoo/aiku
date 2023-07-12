<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 14 Mar 2023 23:44:10 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->


<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faExclamationCircle, faCheckCircle } from "@/../private/pro-solid-svg-icons"
import { faCopy } from "@/../private/pro-light-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faExclamationCircle, faCheckCircle, faCopy);

const props = defineProps<{
    form: any,
    fieldName: string,
    options?: any,
    fieldData?: {
        placeholder?: string
        readonly?: boolean
        copyButton?: boolean
    }
}>()

const copyText = (text: string) => {
    const textarea = document.createElement("textarea")
    textarea.value = text
    document.body.appendChild(textarea)
    textarea.select()
    document.execCommand("copy")
    textarea.remove()
}

</script>
<template>
    <div class="relative">
        <div class="relative">
            <input
                v-model.trim="form[fieldName]"
                :readonly="fieldData.readonly"
                :type="props.options?.type ?? 'text'" @input="form.errors[fieldName] = ''"
                :placeholder="fieldData?.placeholder"
                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md read-only:bg-gray-100 read-only:ring-0 read-only:ring-transparent read-only:text-gray-500"
            />
            <div v-if="fieldData.copyButton" class="absolute inset-y-0 right-0 group cursor-pointer px-1.5 flex justify-center items-center text-gray-600"
                @click="copyText(form[fieldName])">
                <FontAwesomeIcon
                    icon="fal fa-copy"
                    class="text-lg leading-none mr-1 opacity-20 group-hover:opacity-75 group-active:opacity-100"
                    aria-hidden="true"
                />
            </div>
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


