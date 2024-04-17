<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 10 Apr 2024 16:09:45 Central Indonesia Time, Sanur , Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import { faPlus } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref } from 'vue'
import Input from '@/Components/Forms/Fields/Input.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import Select from '@/Components/Forms/Fields/Select.vue';
import Schemaform from '@/Components/SchemaForm/SchemaForm.vue';
library.add(faPlus)

const props = defineProps<{
    provider: object,
    onCloseModal: Function,
    paymentAccountTypes: object
}>()


const form = useForm({})

/* const onSubmit = () => {
    form.post(
        route(props.provider.storeRoute.name, props.provider.storeRoute.parameters), {
        onSuccess: () => { props.onCloseModal() },
    }
    )
} */
</script>


<template>
    <div class="p-2">
        <Schemaform 
            :bluprint="provider?.formData?.blueprint" 
            :form="form" 
            :route="provider?.formData?.route"
            @onSuccess="()=>props.onCloseModal()"
            @onCancel="()=>props.onCloseModal()"
        />
    </div>
</template>
