<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 14 Mar 2023 03:19:52 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import {Head} from '@inertiajs/vue3';

import PageHeading from '@/Components/Headings/PageHeading.vue';
import FieldForm from '@/Components/Forms/FieldForm.vue';

const props = defineProps(['title', 'pageHead', 'formData']);
</script>


<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <div class="px-4 sm:px-6 md:px-4">
        <div class="pb-6">
            <div class="mt-10 divide-y divide-grey-200" v-for="(sectionData,sectionIdx ) in formData['blueprint']" :key="sectionIdx">
                <div class="space-y-1 mb-6 ">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 capitalize">
                        {{ sectionData.title }}
                    </h3>
                    <p v-show="sectionData['subtitle']" class="max-w-2xl text-sm text-gray-500 capitalize">
                        {{ sectionData['subtitle'] }}
                    </p>
                </div>


                <FieldForm
                    class=" pt-4 sm:pt-5 px-6 "
                    v-for="(fieldData,field ) in sectionData.fields"
                    :field="field" :fieldData="fieldData" :args="formData['args']"/>


            </div>


        </div>
    </div>
</template>



