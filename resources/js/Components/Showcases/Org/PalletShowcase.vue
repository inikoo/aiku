<script setup lang='ts'>
import { ref, onMounted } from 'vue'
import JsBarcode from 'jsbarcode'

const props = defineProps<{
    data: {
        data: {
            id: number
            reference: string
            customer_reference: string
            slug: string
            customer_name: string
            location: {
                id: number
                slug: string
                code: string
                tags: []
            }
            state: string
            status: string
            notes: string
            items: []
        }
    }
}>()

// Blueprint: data
const blueprint = [
    {
        name: 'Slug',
        value: props.data.data.slug
    },
    {
        name: 'reference',
        value: props.data.data.reference
    },
    {
        name: 'Customer name',
        value: props.data.data.customer_name
    },
    {
        name: 'Customer reference',
        value: props.data.data.customer_reference

    },
    {
        name: 'Location',
        value: props.data.data.location.code
    },
    {
        name: 'State',
        value: props.data.data.state
    },
    {
        name: 'Status',
        value: props.data.data.status
    },
]


onMounted(() => {
    JsBarcode('#palletBarcode', `pal-${props.data.data.slug}`, {
        lineColor: "rgb(41 37 36)",
        width: 2,
        height: 70,
        displayValue: true
    })
})

</script>


<template>
    <div class="grid max-w-2xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-7xl lg:grid-cols-2 lg:px-8 pt-4">
        <!-- Section: data -->
        <dl class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 sm:gap-y-16 lg:gap-x-8">
            <div v-for="feature in blueprint" :key="feature.name" class="border-t border-gray-200 pt-4">
                <dt class="font-medium">{{ feature.name }}</dt>
                <dd class="mt-2 text-sm text-gray-500">{{ feature.value }}</dd>
            </div>
        </dl>

        <!-- Section: Barcode -->
        <div class="flex justify-end gap-4 sm:gap-6 lg:gap-8">
            <svg id="palletBarcode" class="rounded-lg bg-gray-100" />
        </div>
    </div>
</template>