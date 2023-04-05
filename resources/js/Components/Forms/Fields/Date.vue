<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 05 Apr 2023 11:18:06 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import DatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import {ref} from "vue";

import { faExclamationCircle ,faCheckCircle} from "../../../../private/pro-solid-svg-icons";
import {library} from '@fortawesome/fontawesome-svg-core';
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
library.add(faExclamationCircle,faCheckCircle);


const props = defineProps(['form', 'fieldName','options']);

const handleChange = (form) => {
    if(form.type==='edit'){
        form.clearErrors();

    }
}


const date = ref();


</script>


<template>
    <DatePicker v-model="form[fieldName]"

    />

            <div v-if="form.errors[fieldName] || form.recentlySuccessful " class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <FontAwesomeIcon icon="fas fa-exclamation-circle" v-if="form.errors[fieldName]" class="h-5 w-5 text-red-500" aria-hidden="true" />
                <FontAwesomeIcon icon="fas fa-check-circle" v-if="form.recentlySuccessful" class="mt-1.5  h-5 w-5 text-green-500" aria-hidden="true"/>

            </div>
    <p v-if="form.errors[fieldName]" class="mt-2 text-sm text-red-600" id="email-error">{{ form.errors[fieldName] }}</p>
</template>


