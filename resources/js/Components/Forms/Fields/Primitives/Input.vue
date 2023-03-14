<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Fri, 08 Apr 2022 01:15:30 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Inikoo
  -  Version 4.0
  -->


<script setup>

import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import { faExclamationCircle ,faCheckCircle} from "@/../private/pro-solid-svg-icons";
import {library} from '@fortawesome/fontawesome-svg-core';
library.add(faExclamationCircle,faCheckCircle);

const props = defineProps(['form', 'fieldName','options']);

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
    <div class="mt-1 relative rounded-md shadow-sm">
    <input  @input="handleChange(form)" v-model="form[fieldName]"
           :type="type"
            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" />
        <div v-if="form.errors[fieldName] || form.recentlySuccessful " class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <FontAwesomeIcon icon="fas fa-exclamation-circle" v-if="form.errors[fieldName]" class="h-5 w-5 text-red-500" aria-hidden="true" />
            <FontAwesomeIcon icon="fas fa-check-circle" v-if="form.recentlySuccessful" class="mt-1.5  h-5 w-5 text-green-500" aria-hidden="true"/>

        </div>
    </div>
    <p v-if="form.errors[fieldName]" class="mt-2 text-sm text-red-600" id="email-error">{{ form.errors[fieldName] }}</p>

</template>


