<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 14 Mar 2023 23:44:10 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->


<script setup lang="ts">
import PrimitiveInput from '@/Components/Elements/Fields/PrimitiveInput.vue'

const props = defineProps<{ form, fieldName, options?}>()

let type = 'text'
if (props.options !== undefined && props.options.type) {
    type = props.options.type
}

// For Letters and Words Counter
let showStats = false
if(props.options){
    showStats = props.options.counter
}

</script>
<template>
    <!-- {{ showStats }} -->
    <div class="relative">
        <!-- <input @input="handleChange(form)" v-model="form[fieldName]" :type="type"
            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" /> -->
        <PrimitiveInput v-model="form[fieldName]" :showStats="showStats" :type="type" :form="form" :fieldName="fieldName"/>
        
    </div>
    <p v-if="form.errors[fieldName]" class="mt-2 text-sm text-red-600" id="email-error">{{ form.errors[fieldName] }}</p>
</template>


