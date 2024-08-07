<script setup lang='ts'>

import Table from '@/Components/Table/Table.vue'
import { inject } from 'vue'

defineProps<{
    data: object
    tab: string
}>()

const locale = inject('locale', {})


</script>

<template>
    <div class="h-min">
        <Table :resource="data" :name="tab">
            <template #cell(net_amount)="{ item }">
                <div :class="item.net_amount < 0 ? 'text-red-500' : ''">
                    {{ locale.currencyFormat(item.currency_code, item.net_amount) }}
                </div>
            </template>
        </Table>
    </div>
</template>
