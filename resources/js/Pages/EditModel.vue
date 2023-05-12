<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 14 Mar 2023 03:19:52 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

import PageHeading from '@/Components/Headings/PageHeading.vue';
import FieldForm from '@/Components/Forms/FieldForm.vue';
import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

import { faUserCircle, faKey, faClone, faPaintBrush, faMoonStars, faLightbulbOn, faCheck,faPhone } from "@/../private/pro-light-svg-icons"

library.add(faUserCircle, faKey, faClone, faPaintBrush, faMoonStars, faLightbulbOn, faCheck,faPhone)

const props = defineProps<{
    title: string,
    pageHead: {
        title: string
        exitEdit: {
            route: {
                name: string
                parameters: string[]
            }
        }
    },
    formData: {
        blueprint: Array<{
            title: string
            icon: string
            current: boolean
            fields: Array<
                {
                    name: string,
                    type: string,
                    label: string,
                    value: string | object
                }
            >
        }>,
        args: {
            updateRoute: {
                name: string,
                parameters: string | string[]
            }
        }
    }
}>()


const current = ref(0)
</script>


<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>


    <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="divide-y divide-gray-200 lg:grid grid-flow-col lg:grid-cols-12 lg:divide-y-0 lg:divide-x">
            <aside class="py-0 lg:col-span-3 lg:h-full">
                <!-- <div>
                    <h2
                        class="py-3 pl-2 font-bold leading-7 text-gray-900 sm:truncate lg:text-2xl sm:tracking-tight capitalize">
                        {{ pageHead.title }}
                    </h2>
                </div> -->
                <nav role="navigation" class="space-y-1">
                    <ul>
                        <li v-for="(item, key) in formData['blueprint']" @click="current = key" :class="[
                            key == current
                                ? 'bg-teal-50 border-teal-500 text-teal-700 hover:bg-teal-50 hover:text-teal-700'
                                : 'border-transparent text-gray-900 hover:bg-gray-50 hover:text-gray-900',
                            'cursor-pointer group border-l-4 px-3 py-2 flex items-center text-sm font-medium',
                        ]" :aria-current="key === current ? 'page' : undefined">
                            <FontAwesomeIcon aria-hidden="true" :class="[
                                key === current
                                    ? 'text-teal-500 group-hover:text-teal-500'
                                    : 'text-gray-400 group-hover:text-gray-500',
                                'flex-shrink-0 -ml-1 mr-3 h-6 w-6',
                            ]" :icon="item.icon" />

                            <span class="truncate">{{ item.title }}</span>
                        </li>
                    </ul>
                </nav>
            </aside>

            <div class="px-4 sm:px-6 md:px-4 col-span-9">
                <div class="pb-6">
                    <div class="mt-10 divide-y divide-grey-200" v-for="(sectionData, sectionIdx ) in formData['blueprint']"
                        :key="sectionIdx">
                        <div class="space-y-1 mb-6 ">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 capitalize">
                                {{ sectionData.title }}
                            </h3>
                            <p v-show="sectionData['subtitle']" class="max-w-2xl text-sm text-gray-500 capitalize">
                                {{ sectionData['subtitle'] }}
                            </p>
                        </div>
                        <FieldForm class=" pt-4 sm:pt-5 px-6 " v-for="(fieldData, field ) in sectionData.fields"
                            :field="field" :fieldData="fieldData" :args="formData['args']" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>



