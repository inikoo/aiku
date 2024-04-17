<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 10 Apr 2024 16:09:45 Central Indonesia Time, Sanur , Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import { faPlus } from "@fas"
import { onMounted } from 'vue';
import { library } from "@fortawesome/fontawesome-svg-core"
import SchemaFileds from '@/Components/SchemaForm/SchemaFileds.vue';
import Button from '../Elements/Buttons/Button.vue';
import { get } from 'lodash'

library.add(faPlus)

const props = defineProps(
    {
        form: {
            type: Object,
            default: useForm({}),
            required: false,
        },
        bluprint: {
            type: Object,
            default: [],
            required: true,
        },
        route: {
            type: Object,
        }
    });


const emits = defineEmits<{
    (e: 'onSuccess', value: string): void
    (e: 'onCancel', value: string): void
    (e: 'onError', value: string): void
    (e: 'onBefore', value: string): void
    (e: 'onStart', value: string): void
    (e: 'onProgress', value: string): void
    (e: 'onFinish', value: string): void
}>()

onMounted(() => {
    if (Object.keys(props.form.data()).length === 0) {
        for (const f of props.bluprint) {
            for (const field in f.fields) {
                props.form[field] = props.form[field] ? props.form[field] : null;
                props.form[field] = props.form[field] || null;
            }
        }
    }
});

const onSubmit = () => {
    if (props.route) {
        props.form.post(
            route(props.route.name, props.route.parameters), {
            onBefore: (visit) => { emits('onBefore', visit) },
            onStart: (visit) => { emits('onStart', visit) },
            onProgress: (progress) => { emits('onProgress', progress) },
            onSuccess: (page) => { emits('onSuccess', page) },
            onError: (errors) => { emits('onError', errors) },
            onFinish: visit => { emits('onFinish', visit) },
        }
        )
    }
}

const onCancel = (e) => {
    emits('onCancel',e)
    props.form.reset()
}


</script>


<template>
    <div class="">
        <div v-for="item in bluprint" :key="item.title">
            <div class="mb-3 text-2xl font-medium text-gray-500 capitalize"><span>{{ item.title }}</span></div>
            <hr class="my-5 h-0.5 border-t-0 bg-neutral-100 dark:bg-white/10" />
            <div class="mb-5 flex flex-wrap">
                <div v-for="(fieldData, fieldName) in item.fields" :key="fieldName"
                    :class="`lg:w-${get(fieldData, 'column', 'full')}  sm:w-full md:w-full px-2`">
                    {{ fieldData.column }}
                    <SchemaFileds :field="fieldName" :fieldData="fieldData" :form="form" />
                </div>
            </div>
            <div class="flex justify-end">
                <Button @click="onCancel" label="cancel" type="tertiary" class="mr-1" />
                <Button @click="onSubmit" label="Save" type="save"
                    class="bg-indigo-700 hover:bg-slate-600 border border-slate-500 text-teal-50 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2" />

            </div>
        </div>
    </div>
</template>