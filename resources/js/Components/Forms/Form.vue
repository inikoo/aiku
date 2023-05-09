<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sat, 08 Oct 2022 12:13:04 Central European Summer Time, Mijas, Spain
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
const props = defineProps(
    {
        'form':{

        },
        'layout':{

        },
        'url':{
            type:String,

        },
        'method':{
            type:String,
            default:'post'
        }
    },
);
import SubmitButtons from '@/Components/Forms/SubmitButtons.vue';

function submit() {

    if (props.method === 'post') {
        props.form.post(props.url);
    } else if (props.method === 'put') {
        props.form.put(props.url);
    } else {
        props.form.patch(props.url);
    }

}

</script>

<template>
    <form class="divide-y divide-gray-200 lg:col-span-9" @submit.prevent="submit">

        <div class="p-6 lg:pb-8">
            <div>
                <h2 class="text-lg font-medium leading-6 text-gray-900">{{ layout.title }}</h2>
                <p v-if="layout.notes" class="mt-1 text-sm text-gray-500">{{ layout.notes }}</p>
            </div>
            <slot></slot>
        </div>

        <SubmitButtons :form="form"/>


    </form>
</template>
