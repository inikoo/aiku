<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Feb 2024 19:17:38 CEST Time, Plane Madrid - Mexico City
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { WebUser } from "@/types/web-user";
import { useFormatTime } from '@/Composables/useFormatTime'

defineProps<{
    data: object,
    tab?: string
}>();

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
    </Table>
</template>


