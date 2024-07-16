<script setup lang='ts'>
import OrderSummary from '@/Components/Summary/OrderSummary.vue'
import { FieldOrderSummary } from '@/types/Pallet'

const props = defineProps<{
    data: {
        order_summary: FieldOrderSummary[][]
    }
}>()
    
</script>

<template>
    
    <div class="px-6 py-4 grid grid-cols-2 gap-x-5">
        <!-- <OrderSummary :order_summary="data.order_summary" /> -->
        Recurring Bills Showcase
    </div>
</template>