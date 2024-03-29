<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Webpage} from "@/types/webpage";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import {
    faSignIn, faHome, faNewspaper, faBrowser, faUfoBeam
} from '@fal'
import { library } from "@fortawesome/fontawesome-svg-core";

library.add(
    faSignIn, faHome, faNewspaper, faBrowser, faUfoBeam
)

const props = defineProps<{
    data: object
    tab?: string
}>()


function webpageRoute(webpage: Webpage) {
    console.log(route().current())
    switch (route().current()) {
        case 'grp.org.fulfilments.show.web.websites.show.webpages.index':
            return route(
                'grp.org.fulfilments.show.web.websites.show.webpages.show',
                [
                    route().params['organisation'],
                    route().params['fulfilment'],
                    route().params['website'],
                    webpage.slug
                ]);
    }
}

</script>


<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: webpage }">
            <Link :href="webpageRoute(webpage)" class="specialUnderline">
                {{ webpage['code'] }}
            </Link>
        </template>

        <template #cell(type)="{ item: webpage }">
            <FontAwesomeIcon :icon="webpage.typeIcon" class=""/>
        </template>

        <template #heading(level)="{ item: column }">
            <div class="flex flex-row items-center justify-start">
                <div v-if="typeof column.label === 'object'">
                    <FontAwesomeIcon v-if="column.label.type === 'icon'" :title="capitalize(column.label.tooltip)"
                                     aria-hidden="true" :icon="column.label.data" size="lg" />
                    <FontAwesomeIcon v-else :title="'icon'" aria-hidden="true" :icon="column.label" size="lg" />
                </div>

                <svg v-if="column.sortable" aria-hidden="true" class="w-3 h-3 ml-2" :class="{
                    'text-gray-400': !column.sorted,
                    'text-green-500': column.sorted,
                }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"
                     :sorted="column.sorted">
                    <path v-if="!column.sorted" fill="currentColor"
                          d="M41 288h238c21.4 0 32.1 25.9 17 41L177 448c-9.4 9.4-24.6 9.4-33.9 0L24 329c-15.1-15.1-4.4-41 17-41zm255-105L177 64c-9.4-9.4-24.6-9.4-33.9 0L24 183c-15.1 15.1-4.4 41 17 41h238c21.4 0 32.1-25.9 17-41z" />

                    <path v-if="column.sorted === 'asc'" fill="currentColor"
                          d="M279 224H41c-21.4 0-32.1-25.9-17-41L143 64c9.4-9.4 24.6-9.4 33.9 0l119 119c15.2 15.1 4.5 41-16.9 41z" />

                    <path v-if="column.sorted === 'desc'" fill="currentColor"
                          d="M41 288h238c21.4 0 32.1 25.9 17 41L177 448c-9.4 9.4-24.6 9.4-33.9 0L24 329c-15.1-15.1-4.4-41 17-41z" />
                </svg>
            </div>
        </template>
    </Table>
</template>

