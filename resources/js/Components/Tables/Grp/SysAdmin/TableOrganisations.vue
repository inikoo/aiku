<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Organisation} from "@/types/organisation";
import Icon from "@/Components/Icon.vue";
import {library} from "@fortawesome/fontawesome-svg-core";
import {faStore, faAd} from '@fal'
library.add(faStore, faAd)


const props = defineProps<{
    data: object,
    tab?: string
}>()

function orgRoute(org: Organisation) {
    switch (route().current()) {
        case 'grp.organisations.index':
            return route(
                'grp.org.dashboard.show',
                [org.slug]);

    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: org }">
            <Link :href="orgRoute(org)" class="primaryLink">
                {{ org['slug'] }}
            </Link>
        </template>
      <template #cell(type)="{ item: org }">
        <Icon :data="org['type_icon']" />
      </template>
    </Table>
</template>
