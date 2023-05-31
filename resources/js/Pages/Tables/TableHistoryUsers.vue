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
// import {ref,computed} from 'vue'

// import TableElements from '@/Components/Table/TableElements.vue'

const props = defineProps<{
    data: object
}>()

</script>

<template>
    <Table :resource="data" class="mt-5">
        <template #cell(username)="{ item: user }">
            <template v-if="user.username">{{ user.username }}</template>
        </template>
    </Table>
</template>
