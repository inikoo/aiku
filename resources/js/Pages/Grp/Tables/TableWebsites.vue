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
    data: object,
    tab?:string
}>()


function websiteRoute(website: Website) {
    switch (route().current()) {
        case 'grp.web.websites.index':
            return route(
                'grp.web.websites.show',
                [website.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(slug)="{ item: website }">
            <Link :href="websiteRoute(website)">
                {{ website['slug'] }}
            </Link>
        </template>
    </Table>


</template>


