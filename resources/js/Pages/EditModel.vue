<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 14 Mar 2023 03:19:52 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { capitalize } from "@/Composables/capitalize"

import PageHeading from '@/Components/Headings/PageHeading.vue';
import FieldForm from '@/Components/Forms/FieldForm.vue';
import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faGoogle} from "@fortawesome/free-brands-svg-icons"

import { faUserLock,faBell,faCopyright, faUserCircle, faKey, faClone, faPaintBrush, faMoonStars, faLightbulbOn, faCheck, faPhone, faIdCard, faFingerprint,faLanguage,faAddressBook,faTrashAlt } from '@fal/'

library.add(faUserLock,faBell,faCopyright,faUserCircle, faKey, faClone, faPaintBrush, faMoonStars, faLightbulbOn, faCheck, faPhone, faIdCard, faFingerprint,faLanguage,faAddressBook,faTrashAlt, faGoogle)

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
            button: {
                title: string
                route: string
                disable: boolean
            }
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
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>

    <!-- If overflow-hidden, affect to Multiselect on Address -->
    <div class="rounded-lg shadow">
        <div class="divide-y divide-gray-200 lg:grid grid-flow-col lg:grid-cols-12 lg:divide-y-0 lg:divide-x">

            <!-- Left Tab: Navigation -->
            <aside class="py-0 lg:col-span-3 lg:h-full">
                <nav role="navigation" class="space-y-1">
                    <ul>
                        <li v-for="(item, key) in formData['blueprint']" @click="current = key" :class="[
                            key == current
                                ? 'bg-indigo-50 border-indigo-500 text-indigo-700 hover:bg-indigo-50 hover:text-indigo-700'
                                : 'border-transparent text-gray-900 hover:bg-gray-50 hover:text-gray-900',
                            'cursor-pointer group border-l-4 px-3 py-2 flex items-center text-sm font-medium',
                        ]" :aria-current="key === current ? 'page' : undefined">
                            <FontAwesomeIcon v-if="item.icon" aria-hidden="true" :class="[
                                key === current
                                    ? 'text-indigo-500 group-hover:text-indigo-500'
                                    : 'text-gray-400 group-hover:text-gray-500',
                                'flex-shrink-0 -ml-1 mr-3 h-6 w-6',
                            ]" :icon="item.icon" />

                            <span class="capitalize truncate">{{ item.title }}</span>
                        </li>
                    </ul>
                </nav>
            </aside>

            <!-- Content of forms -->
            <div class="px-4 sm:px-6 md:px-4 col-span-9">
                <div class="divide-y divide-grey-200 flex flex-col">
                    <!-- <div class="space-y-1 mb-6 ">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 capitalize">
                            {{ formData['blueprint'][current].title }}
                        </h3>
                        <p v-show="formData['blueprint'][current]['subtitle']" class="max-w-2xl text-sm text-gray-500 capitalize">
                            {{ formData['blueprint'][current]['subtitle'] }}
                        </p>
                    </div> -->
                    <FieldForm class=" pt-4 sm:pt-5 px-6 " v-for="(fieldData, field ) in formData.blueprint[current].fields"
                        :key="field" :field="field" :fieldData="fieldData" :args="formData.args" />

                    <!-- Button for Authorize Google Drive -->
                    <div class="py-2 px-3 flex justify-end max-w-2xl" v-if="formData.blueprint[current].button">
                        <component :is="formData.blueprint[current].button.disable ? 'div' : 'a'"
                            :href="formData.blueprint[current].button.route" target="_blank" rel="noopener noreferrer"
                            class="px-3 py-1.5 rounded"
                            :class="[formData.blueprint[current].button.disable ? 'bg-indigo-200 cursor-default text-white' : 'text-gray-100 bg-indigo-500 hover:bg-indigo-600']"
                        >
                            {{ formData.blueprint[current].button.title }}
                        </component>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>



