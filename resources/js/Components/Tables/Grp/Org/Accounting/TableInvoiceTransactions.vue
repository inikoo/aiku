<script setup lang='ts'>

import Table from '@/Components/Table/Table.vue'

defineProps<{
    data: object
    tab: string
}>()



</script>

<template>
    <div class="h-min   ">

        <div class="rounded-md">
            <Table :resource="data" :name="tab">
                <template #cell(net_amount)="{ item }">
                    <div :class="item.net_amount < 0 ? 'text-red-500' : ''">
                        {{ item.net_amount }}
                    </div>
                </template>
            </Table>
        </div>

    </div>

</template>
