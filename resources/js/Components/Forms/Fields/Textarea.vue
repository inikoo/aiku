<script setup>

import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import { faExclamationCircle ,faCheckCircle} from '@fas';
import {library} from '@fortawesome/fontawesome-svg-core';
library.add(faExclamationCircle,faCheckCircle);

const props = defineProps(['form', 'fieldName','options', 'fieldData']);

const handleChange = (form) => {
    if(form.fieldType==='edit'){
        form.clearErrors();
    }
}

let type='text'
if(props.options!==undefined && props.options.type ){
    type=props.options.type;
}
</script>


<template>
    <div class="relative">
        <div>
            <label :for="fieldName" class="block text-sm font-medium text-gray-700"></label>
            <div class="rounded-md shadow-sm">
                <textarea
                    v-model.trim="form[fieldName]"
                    :id="fieldName"
                    :name="fieldName"
                    :placeholder="fieldData?.placeholder"
                    rows="3"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
            <div v-if="fieldData.counter" class="grid grid-flow-col text-xs italic text-gray-500 mt-2 space-x-12 justify-start">
                <p class="">
                    <!-- {{ pageBody.layout.profile.fields.about.notes }} -->
                    Letters: {{ form[fieldName].length }}
                </p>
                <p class="">
                    <!-- {{ pageBody.layout.profile.fields.about.notes }} -->
                    Words: {{ form[fieldName].trim().split(/\s+/).filter(Boolean).length }}
                </p>
            </div>
        </div>
        <div v-if="form.errors[fieldName] || form.recentlySuccessful " class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <FontAwesomeIcon icon="fas fa-exclamation-circle" v-if="form.errors[fieldName]" class="h-5 w-5 text-red-500" aria-hidden="true" />
            <FontAwesomeIcon icon="fas fa-check-circle" v-if="form.recentlySuccessful" class="mt-1.5  h-5 w-5 text-green-500" aria-hidden="true"/>
        </div>
    </div>
    <p v-if="form.errors[fieldName]" class="mt-2 text-sm text-red-600" id="email-error">{{ form.errors[fieldName] }}</p>
</template>


