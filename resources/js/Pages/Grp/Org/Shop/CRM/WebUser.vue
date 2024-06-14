<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 24 Nov 2022 10:39:51 Central Indonesia Time, Ubud, Bali, Indonesia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faGlobe } from '@fal'
import { capitalize } from "@/Composables/capitalize"
import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import ShowcaseStats from '@/Components/ShowcaseStats.vue'
import { useFormatTime } from '@/Composables/useFormatTime'
import { trans } from 'laravel-vue-i18n'
import PureInput from '@/Components/Pure/PureInput.vue'


library.add(faGlobe)

const props = defineProps<{
    title: string
    pageHead: TSPageHeading
    data: {}
}>()

const dataCompany = [
    {
        label: 'Contact',
        value: props.data.customer?.contact_name
    },
    {
        label: 'Company Name',
        value: props.data.customer?.company_name
    },
    {
        label: 'Email',
        value: props.data.customer?.email
    },
    {
        label: 'Create At',
        value: useFormatTime(props.data.customer?.created_at)
    },
    {
        label: 'Location',
        value: props.data.customer?.location
    },
]
</script>


<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>

    <div class="grid grid-cols-2 py-4 px-6">

        <!-- Section: field data -->
        <dl v-if="true" class="h-fit grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 sm:gap-y-10 lg:gap-x-8">
            <div class="col-span-2 ">
                <dt class="font-medium">Username</dt>
                <dd class="mt-2 text-sm text-gray-500 text-justify">
                    <PureInput :modelValue="props.data.username" :rows="5" :placeholder="trans('No email.')" disabled />
                </dd>
            </div>

            <div class="col-span-2 ">
                <dt class="font-medium">Email</dt>
                <dd class="mt-2 text-sm text-gray-500 text-justify">
                    <PureInput :modelValue="props.data.email" :rows="5" :placeholder="trans('No email.')" disabled />
                </dd>
            </div>

            <!-- <div class="border-t border-gray-200 pt-4">
                <dt class="font-medium">{{ blueprint.customer_reference.label }}</dt>
                <dd class="mt-2 text-sm text-gray-500 text-justify">{{ blueprint.customer_reference.value }}</dd>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <dt class="font-medium">{{ blueprint.location.label }}</dt>
                <dd class="mt-2 text-sm text-gray-500 text-justify">
                    <Link v-if="blueprint.location.value.route?.name" :href="route(blueprint.location.value.route.name, blueprint.location.value.route.parameters)" class="primaryLink">
                        {{ blueprint.location.value.resource.code }}
                    </Link>
                    <span v-else>{{ blueprint.location.value.resource.code }}</span>
                </dd>
            </div> -->
        </dl>

        <!-- Company Data -->
        <div class="justify-self-end bg-slate-50 px-6 py-4 space-y-4 w-80 border border-gray-200 rounded-md shadow">
            <div class="text-xl font-bold">Company details</div>
            <div v-for="print in dataCompany" class="">
                <div class="font-semibold text-sm">{{ print.label }}</div>
                <div class="text-gray-500">
                    {{ print.value }}
                </div>
            </div>
        </div>
    </div>
        <!-- <pre>{{ data }}</pre> -->
</template>
