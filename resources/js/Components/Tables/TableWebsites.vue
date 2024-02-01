<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Website} from "@/types/website";
import Icon from '@/Components/Icon.vue'

const props = defineProps<{
    data: object,
    tab?:string
}>()


function websiteRoute(website: Website) {
    switch (route().current()) {
        case 'grp.org.shops.show.web.websites.index':
            return route(
                'grp.org.shops.show.web.websites.show',
                [route().params.organisation, route().params.shop, website.slug]
            );
        case 'grp.org.fulfilments.show.websites.index':
            return route(
                'grp.org.fulfilments.show.websites.show',
                [route().params.organisation, route().params.fulfilment, website.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(slug)="{ item: website }">
            <Link :href="websiteRoute(website)" class="specialUnderline">
                {{ website['slug'] }}
            </Link>
        </template>

        <template #cell(state)="{ item: banner }">
            <Icon :data="banner['state_icon']" class="px-1"/>
        </template>

    </Table>


</template>


