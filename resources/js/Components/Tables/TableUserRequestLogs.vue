<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Table from '@/Components/Table/Table.vue'
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue"
import UserAgent from "@/Components/Elements/Info/UserAgent.vue"

const props = defineProps<{
    data: object
}>()

const formatDate = (dateIso: Date) => {
    const date = new Date(dateIso)
    return date.toLocaleString()
}
</script>

<template>
    <Table :resource="data" class="mt-5" name="hst">
        <template #cell(username)="{ item: user }">
            <template v-if="user.username">{{ user.username }}</template>
        </template>
        <template #cell(user_agent)="{ item: user }">
            <UserAgent :data="user.user_agent" />
        </template>
        <template #cell(location)="{ item: user }">
            <AddressLocation :data="user.location" />
        </template>
        <template #cell(datetime)="{ item: user }">
            {{ formatDate(user.datetime) }}
        </template>
    </Table>
</template>
