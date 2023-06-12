<script setup>
import PrimitiveTextarea from '@/Components/Elements/Fields/PrimitiveTextarea.vue';

import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import { faExclamationCircle ,faCheckCircle} from "@/../private/pro-solid-svg-icons";
import {library} from '@fortawesome/fontawesome-svg-core';
library.add(faExclamationCircle,faCheckCircle);

const props = defineProps(['form', 'fieldName','options', 'fieldData']);

const handleChange = (form) => {
    if(form.type==='edit'){
        form.clearErrors();
    }
}

let type='text'
if(props.options!==undefined && props.options.type ){
    type=props.options.type;
}
</script>


<template>
    <div class="mt-1 relative">
        <PrimitiveTextarea v-model="form[fieldName]" showStats="true" />
        <div v-if="form.errors[fieldName] || form.recentlySuccessful " class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <FontAwesomeIcon icon="fas fa-exclamation-circle" v-if="form.errors[fieldName]" class="h-5 w-5 text-red-500" aria-hidden="true" />
            <FontAwesomeIcon icon="fas fa-check-circle" v-if="form.recentlySuccessful" class="mt-1.5  h-5 w-5 text-green-500" aria-hidden="true"/>
        </div>
    </div>
    <p v-if="form.errors[fieldName]" class="mt-2 text-sm text-red-600" id="email-error">{{ form.errors[fieldName] }}</p>
</template>


