<!--
  - Author: Raul Perusquia <raul@inikoo.com>  
  - Created: Wed, 08 Jan 2025 22:09:39 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2025, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'

import { capitalize } from "@/Composables/capitalize"
import { ref } from 'vue'

import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import Table from "@/Components/Table/Table.vue"
import { useLocaleStore } from '@/Stores/locale'
import { RecurringBill } from '@/types/recurring_bill'

import { useFormatTime } from '@/Composables/useFormatTime'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faReceipt } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faReceipt)


const props = defineProps<{
    title: string,
    pageHead: TSPageHeading
    data: {}

}>()

const locale = useLocaleStore();

function webUserRoute(webUser: {}) {
    switch (route().current()) {
        default:
            return route(
                'retina.sysadmin.web-users.show',
                [
                webUser.slug
                ])
    }
}

</script>


<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <Table :resource="data" class="mt-5">
        <template #cell(username)="{ item: webUser }">
            <Link :href="webUserRoute(webUser)" class="primaryLink py-0.5">
            {{ webUser.username }}
            </Link>
        </template>

        <!-- Column: Date -->
        <template #cell(type)="{ item }">
            <div class="text-center">
            <!-- {{ item.type }} -->
                <FontAwesomeIcon :icon='item.type?.icon?.icon' v-tooltip="item.type?.icon?.tooltip" :class='item.type?.icon?.class' fixed-width aria-hidden='true' />
            </div>
        </template>

        <!-- Column: Date -->
        <template #cell(date)="{ item }">
            <div class="text-gray-500 text-right">
                {{ useFormatTime(item.date, { localeCode: locale.language.code, formatTime: "aiku" }) }}
            </div>
        </template>

        <!-- Column: Net -->
        <template #cell(net_amount)="{ item: webUser }">
            <div class="text-gray-500">
                {{ useLocaleStore().currencyFormat(webUser.currency_code, webUser.net_amount) }}
            </div>
        </template>

        <!-- Column: Total -->
        <template #cell(total_amount)="{ item: webUser }">
            <div :class="webUser.total_amount >= 0 ? 'text-gray-500' : 'text-red-400'">
                {{ useLocaleStore().currencyFormat(webUser.currency_code, webUser.total_amount) }}
            </div>
        </template>
    </Table>
</template>