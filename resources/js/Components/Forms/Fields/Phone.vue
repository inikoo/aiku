<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 14 Mar 2023 23:52:06 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import { faExclamationCircle, faCheckCircle } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { VueTelInput } from 'vue-tel-input'
import 'vue-tel-input/vue-tel-input.css'
import { ref } from 'vue'
library.add(faExclamationCircle, faCheckCircle)

const props = defineProps(['form', 'fieldName', 'options', 'fieldData'])

let defaultCountry = null
if (props.options !== undefined && props.options.defaultCountry) {
    defaultCountry = props.options.defaultCountry
}

const handleChange = (number, phoneObject) => {
    props.form.phone = phoneObject.number || ''
    // console.log(props.form.phone)
}

const phone = ref(props.form[props['fieldName']])
</script>

<template>
    <div class="relative rounded-md shadow-sm">
    <!-- {{ phone }} --- {{ form.phone }} -->
        <VueTelInput
            @on-input="handleChange"
            v-model="phone"
            inputOptions.placeholder="''"
            :defaultCountry="defaultCountry"
        />

        <div v-if="form.errors[fieldName] || form.recentlySuccessful " class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <FontAwesomeIcon icon="fas fa-exclamation-circle" v-if="form.errors[fieldName]" class="h-5 w-5 text-red-500" aria-hidden="true"/>
            <FontAwesomeIcon icon="fas fa-check-circle" v-if="form.recentlySuccessful" class="mt-1.5  h-5 w-5 text-green-500" aria-hidden="true"/>
        </div>
    </div>
    <p v-if="form.errors[fieldName]" class="mt-2 text-sm text-red-600" id="email-error">{{ form.errors[fieldName] }}</p>

</template>



