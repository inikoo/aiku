<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Thu, 25 May 2023 15:03:05 Central European Summer Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import { useFormatTime } from '@/Composables/useFormatTime'
import { routeType } from '@/types/route'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faLink} from '@far'
import { faSync, faCalendarAlt, faEnvelope, faPhone } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'

library.add(faLink, faSync, faCalendarAlt, faEnvelope, faPhone)

const props = defineProps<{
    data: {
        customer: object
        updateRoute: routeType

    },
    tab: string
}>()




</script>

<template>
    <!-- Section: Stats box -->
    <div class="px-4 py-5 md:px-6 lg:px-8 grid grid-cols-2 gap-x-8 gap-y-3">

     
        
        <!-- Section: Profile box -->
        <div >
            <h2 class="sr-only">Customer profile</h2>
            <div class="rounded-lg shadow-sm ring-1 ring-gray-900/5">
                <dl class="flex flex-wrap">
                    <!-- Profile: Header -->
                    <div class="flex w-full py-6">
                        <div v-if="data.number_active_clients" class="flex-auto pl-6">
                            <dt class="text-sm font-semibold leading-6 text-gray-900">Total Clients</dt>
                            <dd class="mt-1 text-base font-semibold leading-6 text-gray-900">{{ data.number_active_clients || 0 }}</dd>
                        </div>
                        <div class="flex-none self-end px-6 pt-4">
                            <dt class="sr-only">Reference</dt>
                            <dd class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                {{ data.reference }}
                            </dd>
                        </div>
                    </div>
                    
                    <!-- Section: Field -->
                    <div class="flex flex-col gap-y-3 border-t border-gray-900/5 w-full py-6">
                        <!-- Field: Contact name -->
                        <div v-if="data.contact_name" class="flex items-center w-full flex-none gap-x-4 px-6">
                            <dt v-tooltip="'Contact name'" class="flex-none">
                                <span class="sr-only">Contact name</span>
                                <FontAwesomeIcon icon='fal fa-user' class='text-gray-400' fixed-width aria-hidden='true' />
                            </dt>
                            <dd class="text-gray-500">{{ data.contact_name }}</dd>
                        </div>

                        <!-- Field: Contact name -->
                        <div v-if="data.company_name" class="flex items-center w-full flex-none gap-x-4 px-6">
                            <dt v-tooltip="'Company name'" class="flex-none">
                                <span class="sr-only">Company name</span>
                                <FontAwesomeIcon icon='fal fa-building' class='text-gray-400' fixed-width aria-hidden='true' />
                            </dt>
                            <dd class="text-gray-500">{{ data.company_name }}</dd>
                        </div>

                        <!-- Field: Created at -->
                        <div v-if="data.customer?.created_at" class="flex items-center w-full flex-none gap-x-4 px-6">
                            <dt v-tooltip="'Created at'" class="flex-none">
                                <span class="sr-only">Created at</span>
                                <FontAwesomeIcon icon='fal fa-calendar-alt' class='text-gray-400' fixed-width aria-hidden='true' />
                            </dt>
                            <dd class="text-gray-500">
                                <time datetime="2023-01-31">{{ useFormatTime(data.customer?.created_at) }}</time>
                            </dd>
                        </div>
                        
                        <!-- Field: Email -->
                        <div v-if="data.customer?.email" class="flex items-center w-full flex-none gap-x-4 px-6">
                            <dt v-tooltip="'Email'" class="flex-none">
                                <span class="sr-only">Email</span>
                                <FontAwesomeIcon icon='fal fa-envelope' class='text-gray-400' fixed-width aria-hidden='true' />
                            </dt>
                            <dd class="text-gray-500">{{ data.customer?.email }}</dd>
                        </div>
                        
                        <!-- Field: Phone -->
                        <div v-if="data.customer?.phone" class="flex items-center w-full flex-none gap-x-4 px-6">
                            <dt v-tooltip="'Phone'" class="flex-none">
                                <span class="sr-only">Phone</span>
                                <FontAwesomeIcon icon='fal fa-phone' class='text-gray-400' fixed-width aria-hidden='true' />
                            </dt>
                            <dd class="text-gray-500">{{ data.customer?.phone }}</dd>
                        </div>
                    </div>
                </dl>
            </div>
        </div>


    </div>
</template>

