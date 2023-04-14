<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Department} from "@/types/department";
import NewItem from "@/Components/NewItem.vue";
import { ref } from "vue";
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


function departmentRoute(department: Department) {
    switch (route().current()) {
        case 'shops.show.catalogue.hub':
            return route(
                'shops.show.catalogue.hub.departments.show',
                [route().params['shop'], department.slug]);
        default:
            return route(
                'catalogue.hub.departments.show',
                [department.slug]);
    }
}
const showSearchDialog = ref(false);

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
    <Table :resource="data.table" :name="'dep'" class="mt-5">
        <template #cell(code)="{ item: department }">
            <Link :href="departmentRoute(department)">
                {{ department['code'] }}
            </Link>
        </template>

    </Table>
</template>


