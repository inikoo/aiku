<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import { User } from "@/types/user";
import { trans } from "laravel-vue-i18n";
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue";
import UserAgent from "@/Components/Elements/Info/UserAgent.vue";
// import {ref,computed} from 'vue'

// import TableElements from '@/Components/Table/TableElements.vue'

const props = defineProps<{
    data: object,
    tab?: string
}>()

const formatDate = (dateIso: Date) => {
    const date = new Date(dateIso)
    const year = date.getFullYear()
    const month = (date.getMonth() + 1).toString().padStart(2, '0')
    const day = date.getDate().toString().padStart(2, '0')

    const hours = date.getHours().toString()
    const minutes = date.getMinutes().toString()

    return `${year}-${month}-${day} ${hours}:${minutes}`
}
</script>

<template>
    <Table :resource="data" class="mt-5" :name="tab">
        <template #cell(date)="{ item: user }">
            {{ formatDate(user.datetime) }}
        </template>
    </Table>
</template>
