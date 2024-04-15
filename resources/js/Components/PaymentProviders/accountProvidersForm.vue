<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 10 Apr 2024 16:09:45 Central Indonesia Time, Sanur , Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { useForm, router  } from '@inertiajs/vue3';
import { faPlus } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref } from 'vue'
import Input from '@/Components/Forms/Fields/Input.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import Select from '@/Components/Forms/Fields/Select.vue';
library.add(faPlus)

const props = defineProps<{
    provider: object,
    onCloseModal : Function,
    paymentAccountTypes : object
}>()


const form = useForm({
    code: '',
    name: '',
    account_type : ''
})

const onSubmit = () => {
    form.post(
        route(props.provider.storeRoute.name,props.provider.storeRoute.parameters),{
            onSuccess: () => {props.onCloseModal()},
        }
    )
}
console.log(props.paymentAccountTypes)

</script>


<template>
    <div class="p-2">
        <div class="text-sm py-2">Code</div>
        <Input :form="form" fieldName="code" :fieldData="{ placeholder: 'Enter code' }" />

    </div>
    <div class="p-2">
        <div class="text-sm py-2">Name</div>
        <Input :form="form" fieldName="name" :fieldData="{ placeholder: 'Enter name' }" />
    </div>
    <div class="p-2">
        <div class="text-sm py-2">Type</div>
        <Select :form="form" fieldName="account_type" :options="paymentAccountTypes" :fieldData="{ placeholder: 'Enter type' }" />
    </div>
    <div class="p-2">
        <Button full @click="onSubmit" label="Submit" type="save"
            class="bg-indigo-700 hover:bg-slate-600 border border-slate-500 text-teal-50 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2" />
    </div>
</template>
