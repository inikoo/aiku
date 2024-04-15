<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 10 Apr 2024 16:09:45 Central Indonesia Time, Sanur , Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { faPlus } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref } from 'vue'
import PureInput from '@/Components/Pure/PureInput.vue';
import Button from '@/Components/Elements/Buttons/Button.vue'
library.add(faPlus)

const props = defineProps<{
    provider: object,
}>()


const form = useForm({
    code: null,
    name: null
})

const onSubmit = () => {
    console.log(props.props)
    console.log(form.data())
}


</script>


<template>
    <div class="p-2">
        <div class="text-sm py-2">Code</div>
        <PureInput :modelValue="form.code" placeholder="code" />
    </div>
    <div class="p-2">
        <div class="text-sm py-2">Name</div>
        <PureInput :modelValue="form.name" placeholder="name" />
    </div>
    <div class="p-2">
        <Button full @click="onSubmit" label="Submit" type="save"
            class="bg-indigo-700 hover:bg-slate-600 border border-slate-500 text-teal-50 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2" />
    </div>
</template>