<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 14 Mar 2023 23:44:10 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->


<script setup lang="ts">
import PrimitiveInput from '@/Components/Elements/Fields/PrimitiveInput.vue'

const props = defineProps<{
    form: any,
    fieldName: string,
    options?: any,
    fieldData?: {
        placeholder: string
    }
}>()

// let type = 'text'
// if (props.options !== undefined && props.options.type) {
//     type = props.options.type
// }

// // For Letters and Words Counter
// let showStats = false
// if(props.options){
//     showStats = props.options.counter
// }

</script>
<template>
    <div class="relative">
        <PrimitiveInput v-model="form[fieldName]" :showStats="props.options?.counter ?? false" :type="props.options?.type ?? 'text'" :form="form" :fieldName="fieldName" :placeholder="fieldData.placeholder"/>
    </div>
    <p v-if="form.errors[fieldName]" class="mt-2 text-sm text-red-600" id="email-error">{{ form.errors[fieldName] }}</p>
</template>


