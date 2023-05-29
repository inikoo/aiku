<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Website} from "@/types/website";

const props = defineProps<{
    data: object
}>()


function websiteRoute(website: Website) {
    switch (route().current()) {
        case 'websites.index':
            return route(
                'websites.show',
                [website.slug]);
        case 'shops.show.websites.index':
            return route(
                'shops.show.websites.show',
                [website.shop_slug,website.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="'webs'" class="mt-5">
        <template #cell(code)="{ item: website }">
            <Link :href="websiteRoute(website)">
                {{ website['code'] }}
            </Link>
        </template>
    </Table>


</template>


