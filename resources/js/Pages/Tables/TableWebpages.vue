<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Webpage} from "@/types/webpage";

const props = defineProps<{
    data: object
    tab?: string
}>()


function webpageRoute(webpage: Webpage) {
    switch (route().current()) {
        case 'webpages.index':
            return route(
                'webpages.show',
                [webpage.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: webpage }">
            <Link :href="webpageRoute(webpage)">
                {{ webpage.code }}
            </Link>
        </template>
    </Table>


</template>


