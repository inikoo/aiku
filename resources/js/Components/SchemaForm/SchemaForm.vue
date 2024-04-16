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
    });

    onMounted(() => {
    if (Object.keys(props.form.data()).length === 0) {
        console.log('masikk');
        for (const f of props.bluprint) {
            for (const field in f.fields) {
                props.form[field] = props.form[field] ? props.form[field] : null;
                props.form[field] = props.form[field] || null; 
            }
        }
    }
    console.log(props.form);
});


</script>


<template>
    <div class="p-2">
        <div v-for="item in bluprint" :key="item.title">
            <div class="mb-3 text-xl"><span>{{ item.title }}</span></div>
            <div v-for="(fieldData, fieldName) in item.fields" :key="fieldName">
                <SchemaFileds :field="fieldName" :fieldData="fieldData" :form="form"/>
            </div>
        </div>
    </div>
</template>