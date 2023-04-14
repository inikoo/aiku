<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Family} from "@/types/family";
import { ref } from "vue";
import NewItem from "@/Components/NewItem.vue";
import Button from "@/Components/Elements/Buttons/Button.vue";

const props = defineProps<{
    data: {
        table: object
        createInlineModel?: {
            buttonLabel: string,
            dialog: {
                title: string
                saveLabel: string
                cancelLabel: string
            }
        }
    }
}>();

const showSearchDialog = ref(false);

function familyRoute(family: Family) {
    switch (route().current()) {
        case 'shops.show.catalogue.hub.families.index':
            return route(
                'shops.show.catalogue.hub.families.show',
                [route().params['shop'], family.slug]);
        case 'shops.show.catalogue.hub.departments.show':
            return route(
                'shops.show.catalogue.hub.departments.show.families.show',
                [route().params['shop'],route().params['department'], family.slug]);
        default:
            return route(
                'catalogue.hub.families.show',
                [family.slug]);
    }
}

</script>

<template>
    <span v-if="data.createInlineModel"
          class="hidden sm:block text-end">
                <Button v-on:click="showSearchDialog = !showSearchDialog" type="secondary" action="create"
                        class="capitalize">
                 {{ data.createInlineModel.buttonLabel }}
                </Button>
        <NewItem :data="data.createInlineModel.dialog" v-if="showSearchDialog" v-on:close="showSearchDialog = false">
        </NewItem>
    </span>

    <Table :resource="data.table" :name="'fam'" class="mt-5">
        <template #cell(code)="{ item: family }">
            <Link :href="familyRoute(family)">
                {{ family['code'] }}
            </Link>
        </template>
    </Table>
</template>


