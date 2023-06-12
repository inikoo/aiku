<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 14 Mar 2023 19:09:33 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head, useForm} from '@inertiajs/vue3';

import Select from '@/Components/Forms/Fields/Select.vue'
import PageHeading from '@/Components/Headings/PageHeading.vue';

const props = defineProps<{
    title: string,
    pageHead: object,
    formData: {
        blueprint: object;
        route: {
            name: string,
            arguments?: Array<string>
        };
    }
}>()


import Input from '@/Components/Forms/Fields/Input.vue';
import Phone from '@/Components/Forms/Fields/Phone.vue';
import Date from '@/Components/Forms/Fields/Date.vue';
import {trans} from "laravel-vue-i18n";
import Address from "@/Components/Forms/Fields/Address.vue";
import Country from "@/Components/Forms/Fields/Country.vue";
import Currency from "@/Components/Forms/Fields/Currency.vue";
import { capitalize } from "@/Composables/capitalize"

const getComponent = (componentName) => {

    const components = {
        'input': Input,
        'phone': Phone,
        'date': Date,
        'select': Select,
        'address':Address,
        'country': Country,
        'currency': Currency,
    };
    return components[componentName] ?? null;

};

let fields = {};
Object.entries(props.formData.blueprint).forEach(([, val]) => {
    Object.entries(val['fields']).forEach(([fieldName, fieldData]) => {
        fields[fieldName] = fieldData['value'];
    });
});

const form = useForm(fields);

const handleFormSubmit = () => {
    form.post(route(
        props.formData.route.name,
        props.formData.route.arguments
));
};


</script>

<template layout="App">
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>

    <form class="space-y-8 pb-32 px-5" @submit.prevent="handleFormSubmit">

        <div v-for="(sectionData,sectionIdx ) in formData['blueprint']" :key="sectionIdx" class="mt-10 divide-y divide-blue-200">
            <div class="space-y-1">
                <h3 class="text-lg leading-6 font-medium text-gray-900 capitalize">
                    {{ sectionData.title }}
                </h3>
                <p v-show="sectionData['subtitle']" class="max-w-2xl text-sm text-gray-500">
                    {{ sectionData['subtitle'] }}
                </p>
            </div>
            <div class="mt-2 pt-4 sm:pt-5 ">

                <div v-for="(fieldData,fieldName ) in sectionData.fields" class="mt-1 divide-y divide-red-200">
                    <dl class="divide-y divide-green-200  ">
                        <div class="pb-4 sm:pb-5 sm:grid sm:grid-cols-3 sm:gap-4 ">
                            <dt class="text-sm font-medium text-gray-500 capitalize">
                                {{ fieldData.label }}
                            </dt>

                            <dd class="sm:col-span-2  ">
                                <div class="mt-1 flex text-sm text-gray-900 sm:mt-0">
                                    <div class=" relative  flex-grow">

                                        <!-- Dynamic component -->
                                        <component
                                            :is="getComponent(fieldData['type'])"
                                            :form="form"
                                            :fieldName="fieldName"
                                            :options="fieldData['options']"
                                            :fieldData="fieldData"
                                        >
                                        </component>
                                    </div>
                                    <span class="ml-4 flex-shrink-0 w-5 ">

                                    </span>
                                    <span class="ml-2 flex-shrink-0">

                                     </span>
                                </div>

                            </dd>
                        </div>
                    </dl>
                </div>

            </div>
        </div>

        <div class="pt-5 border-t-2 border-indigo-500">
            <div class="flex justify-end">

                <button type="submit" :disabled="form.processing"
                        class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ trans('Save') }}
                </button>
            </div>
        </div>

    </form>

</template>

