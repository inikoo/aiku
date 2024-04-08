<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 02 Dec 2023 04:03:21 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {  Head, useForm } from '@inertiajs/vue3'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faExclamationCircle, faCheckCircle, faAsterisk } from '@fas'
import { faPhone } from '@fal'
import { library } from "@fortawesome/fontawesome-svg-core"
import Input from '@/Components/Forms/Fields/Input.vue'
import SenderEmail from '@/Components/Forms/Fields/SenderEmail.vue'
import Select from '@/Components/Forms/Fields/Select.vue'
import Phone from '@/Components/Forms/Fields/Phone.vue'
import Date from '@/Components/Forms/Fields/Date.vue'
import { trans } from "laravel-vue-i18n"
import Address from "@/Components/Forms/Fields/Address.vue"
import Radio from '@/Components/Forms/Fields/Radio.vue'
import Country from "@/Components/Forms/Fields/Country.vue"
import Currency from "@/Components/Forms/Fields/Currency.vue"
import InputWithAddOn from '@/Components/Forms/Fields/InputWithAddOn.vue'
import Password from "@/Components/Forms/Fields/Password.vue"
import CustomerRoles from '@/Components/Forms/Fields/CustomerRoles.vue'
import JobPosition from '@/Components/Forms/Fields/JobPosition.vue'
import EmployeePosition from '@/Components/Forms/Fields/EmployeePosition.vue'
import Interest from '@/Components/Forms/Fields/Interest.vue'
import Toggle from '@/Components/Forms/Fields/Toggle.vue'

import { ref, onMounted } from 'vue'
import Textarea from "@/Components/Forms/Fields/Textarea.vue"
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from '@/Composables/capitalize'
import { PageHeading as PageHeadingTypes } from '@/types/PageHeading'

library.add(faExclamationCircle, faAsterisk, faCheckCircle, faPhone)

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    formData: {
        blueprint: {
            title?: string
            subtitle?: string
            icon?: string | string[]
            fields: any  // dynamic key
        }[]
        route: {
            name: string
            parameters?: Array<string>
        };
    }
}>()

console.log(props)

const getComponent = (componentName: string) => {
    const components: any = {
        'input': Input,
        'inputWithAddOn': InputWithAddOn,
        'phone': Phone,
        'date': Date,
        'select': Select,
        'address': Address,
        'radio': Radio,
        'country': Country,
        'currency': Currency,
        'password': Password,
        'customerRoles': CustomerRoles,
        'textarea': Textarea,
        'toggle': Toggle,
        'jobPosition': JobPosition,
        'senderEmail': SenderEmail,
        'employeePosition': EmployeePosition,
        'interest': Interest,
    }
    return components[componentName] ?? null
}

let fields: any = {}
Object.entries(props.formData.blueprint).forEach(([, val]) => {
    Object.entries(val.fields).forEach(([fieldName, fieldData]: any) => {
        fields[fieldName] = fieldData.value
    })
})

const form = useForm(fields);

const handleFormSubmit = () => {
    form.post(route(
        props.formData.route.name,
        props.formData.route.parameters
    ))
}

const buttonRefs = ref([])
const tabActive: any = ref({})

const handleIntersection = (index: number) => (entries: any) => {
    const [entry] = entries
    tabActive.value[`${index}`] = entry.isIntersecting
}

onMounted(() => {
    // To indicate active state that on viewport
    buttonRefs.value.forEach((element: any, index) => {
        const observer = new IntersectionObserver(handleIntersection(index))
        observer.observe(element)

        // Clean up the observer when the component is unmounted
        element.cleanupObserver = () => {
            observer.disconnect()
        }
    })

    // Clean up all the observers when the component is unmounted
    return () => {
        buttonRefs.value.forEach((button: any) => button.cleanupObserver())
    }
})

</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    
    <div class="rounded-lg bg-white shadow">
        <div class="divide-y divide-gray-200 lg:grid grid-flow-col lg:grid-cols-12 lg:divide-y-0 lg:divide-x">

            <!-- Left Tab: Left navigation -->
            <aside v-if="true" class="bg-gray-50 py-0 lg:col-span-3 lg:h-full">
                <div class="sticky top-16">
                    <!-- <template v-for="(item, key) in formData['blueprint']">
                        <div v-if="item.title || item.icon" @click="jumpToElement(`field${key}`)" :class="[
                            tabActive[key]
                                ? 'navigationSecondActive'
                                : 'navigationSecond',
                            'cursor-pointer group border-l-4 px-3 py-2 flex items-center text-sm font-medium',
                        ]">
                            <FontAwesomeIcon v-if="item.icon" aria-hidden="true" class="flex-shrink-0 -ml-1 mr-3 h-6 w-6"
                                :class="[tabActive[key]
                                    ? 'text-gray-400 group-hover:text-gray-500'
                                    : 'text-gray-400',
                                ]"
                                :icon="item.icon" />
                            <span class="capitalize truncate">{{ item.title }}</span>
                        </div>
                    </template> -->
                </div>
            </aside>

            <!-- Main form -->
            <form class="px-4 sm:px-6 md:px-10 col-span-9 gap-y-8 pb-8 divide-y divide-gray-200"
                @submit.prevent="handleFormSubmit">
                <template v-for="(sectionData, sectionIdx ) in formData['blueprint']" :key="sectionIdx">
                    <!-- If Section: all fields is not hidden -->
                    <div v-if="!(Object.values(sectionData.fields).every((field: any) => field.hidden))" class="relative py-4">
                        <!-- Helper: Section click -->
                        <div class="sr-only absolute -top-16" :id="`field${sectionIdx}`" />
                        <!-- Title -->
                        <div class="flex items-center gap-x-2" ref="buttonRefs">
                            <FontAwesomeIcon v-if="sectionData.icon" :icon='sectionData.icon' class='' aria-hidden='true' />
                            <h3 v-if="sectionData.title" class="text-lg leading-6 font-medium text-gray-700 capitalize">
                                {{ sectionData.title }}
                            </h3>
                            <p v-if="sectionData.subtitle" class="max-w-2xl text-sm text-gray-500">
                                {{ sectionData.subtitle }}
                            </p>
                        </div>
                        <div class="mt-2 pt-4 sm:pt-5">
                            <template v-for="(fieldData, fieldName, index ) in sectionData.fields" :key="index">
                                <!-- If Field is not hidden = true -->
                                <div v-if="!fieldData.hidden" class="mt-1 ">
                                    <dl class="divide-y divide-green-200  ">
                                        <div class="pb-4 sm:pb-5 sm:grid sm:grid-cols-3 sm:gap-4"
                                            :class="fieldData.full ? '' : 'max-w-2xl'"
                                        >
                                            <!-- Title of Field -->
                                            <dt class="text-sm font-medium text-gray-500 capitalize">
                                                <div class="inline-flex items-start leading-none">
                                                    <!-- Icon: Required -->
                                                    <FontAwesomeIcon v-if="fieldData.required" :icon="['fas', 'asterisk']"
                                                        class="font-light text-[12px] text-red-400 mr-1" />
                                                    <span>{{ fieldData.label }}</span>
                                                </div>
                                            </dt>
                                            <!-- Field (Full: to full the component field i.e create Prospects Mailshot) -->
                                            <dd :class="fieldData.full ? 'sm:col-span-3' : 'sm:col-span-2'">
                                                <div class="mt-1 flex text-sm text-gray-700 sm:mt-0">
                                                    <div class="relative flex-grow">
                                                        <!-- Dynamic component -->
                                                        <component :is="getComponent(fieldData['type'])" :form="form"
                                                            :fieldName="fieldName" :options="fieldData['options']"
                                                            :fieldData="fieldData" :key="index">
                                                        </component>
                                                    </div>
                                                </div>
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Button -->
                <div class="pt-5 border-t-2 border-orange-500">
                    <div class="flex justify-end">
                        <Button type="submit" :disabled="form.processing" :style="'primary'" size="m" icon="fas fa-save" @click="handleFormSubmit">
                            {{ trans('Save') }}
                        </Button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</template>
