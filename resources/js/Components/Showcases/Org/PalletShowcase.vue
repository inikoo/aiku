<script setup lang='ts'>
import { onMounted } from 'vue'
import JsBarcode from 'jsbarcode'
import { Link } from '@inertiajs/vue3'
import PureTextarea from '@/Components/Pure/PureTextarea.vue'
import { trans } from 'laravel-vue-i18n'
import { routeType } from '@/types/route'
import PureTimeline from '@/Components/Pure/PureTimeline.vue'

const props = defineProps<{
    data: {
        data: {
            id: number
            reference: string
            customer_reference: string
            slug: string
            customer: {
                name: string
                route: routeType
            }
            pallet_delivery_id: {}
            pallet_return_id: {}
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
const blueprint = {
    note: {
        label: 'Note',
        value: props.data.data.notes || '-'
    },
    reference: {
        label: 'Reference',
        value: props.data.data.reference || '-'
    },
    customer: {
        label: 'Customer',
        value: props.data.data.customer || '-'
    },
    // customer_reference: {
    //     label: "Customer's pallet",
    //     value: props.data.data.customer_reference || '-'
    // },
    location: {
        label: 'Location',
        value: props.data.data.location || '-'
    },
    // state: {
    //     label: 'State',
    //     value: props.data.data.state || '-'
    // },
    // status: {
    //     label: 'Status',
    //     value: props.data.data.status || '-'
    // },
    items: {
        label: 'Items',
        value: props.data.data.items || '-'
    },
}


onMounted(() => {
    if(props.data.data.slug){
        JsBarcode('#palletBarcode', `pal-${props.data.data.slug}`, {
            lineColor: "rgb(41 37 36)",
            width: 2,
            height: 70,
            displayValue: true
        })
    }
})

const xxxtimeline = [
    {
        label: 'Delivery',
        icon: 'fal fa-truck-couch',
        tooltip: 'Arrived at warehouse',
        timestamp: new Date(),
    },
    {
        label: 'Moved',
        icon: 'fal fa-receipt',
        tooltip: 'Move to location 1A4K',
        timestamp: new Date(),
        current: true
    },
    {
        label: 'Returned',
        icon: 'fal fa-sign-out-alt',
        tooltip: 'On send to customer',
        timestamp: new Date(),
    },
]
</script>


<template>
    <!-- <pre>{{ data }}</pre> -->
    <div class="grid max-w-2xl grid-cols-1 gap-x-8 gap-y-4 lg:gap-y-16 lg:max-w-7xl lg:grid-cols-2 px-4 lg:px-8 pb-10 pt-4">
        <div class="col-span-2 w-full pb-4 border-b border-gray-300">
            <PureTimeline :options="xxxtimeline" :slidesPerView="xxxtimeline.length" color="#6366f1" />
        </div>

        <!-- Section: field data -->
        <dl class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 sm:gap-y-8 lg:gap-x-8">
            <div class="col-span-2 ">
                <dt class="font-medium">{{ blueprint.note.label }}</dt>
                <dd class="mt-2 text-sm text-gray-500 text-justify">
                    <PureTextarea :modelValue="blueprint.note.value" :rows="5" :placeholder="trans('No note from customer.')" disabled />
                </dd>
            </div>
            
            <div class="border-t border-gray-200 pt-4">
                <dt class="font-medium">{{ blueprint.reference.label }}</dt>
                <dd class="mt-2 text-sm text-gray-500 text-justify">{{ blueprint.reference.value }}</dd>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <dt class="font-medium">{{ blueprint.customer.label }}</dt>
                <dd class="mt-2 text-sm text-gray-500 text-justify">
                    <Link :href="route(blueprint.customer.value.route.name, blueprint.customer.value.route.parameters)" class="primaryLink">
                    <!-- <Link :href="'#'" class="primaryLink"> -->
                        {{ blueprint.customer.value.name }}
                    </Link>
                </dd>
            </div>

            <!-- Field: Items -->
            <div class="border-t border-gray-200 pt-4">
                <dt class="font-medium">{{ blueprint.items.label }}</dt>
                <dd class="mt-2 text-sm text-gray-500 text-justify">
                    <span v-if="blueprint.items.value.length">{{ blueprint.items.value }}</span>
                    <span v-else class="text-gray-400 italic">No items in this pallet.</span>
                </dd>
            </div>
            <!-- <div class="border-t border-gray-200 pt-4">
                <dt class="font-medium">{{ blueprint.customer_reference.label }}</dt>
                <dd class="mt-2 text-sm text-gray-500 text-justify">{{ blueprint.customer_reference.value }}</dd>
            </div> -->

            <div class="border-t border-gray-200 pt-4">
                <dt class="font-medium">{{ blueprint.location.label }}</dt>
                <dd class="mt-2 text-sm text-gray-500 text-justify">
                    <Link v-if="blueprint.location.value.route?.name" :href="route(blueprint.location.value.route.name, blueprint.location.value.route.parameters)" class="primaryLink">
                        {{ blueprint.location.value.resource.code }}
                    </Link>
                    <span v-else>{{ blueprint?.location?.value?.resource?.code }}</span>
                </dd>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <dt class="font-medium">Info</dt>
                <dd class="mt-2 text-sm text-gray-500 text-justify">State: {{ data.data.state }}</dd>
                <dd class="mt-2 text-sm text-gray-500 text-justify">Status: {{ data.data.status }}</dd>
            </div>


            <!-- <div class="border-t border-gray-200 pt-4">
                <dt class="font-medium">Delivery</dt>
                <dd class="mt-2 text-sm text-gray-500 text-justify">{{ props.data.data.pallet_delivery_id }}</dd>
            </div> -->
        </dl>

        <!-- Section: Barcode -->
        <div class="row-start-1 lg:row-start-auto flex justify-center lg:justify-end gap-4 sm:gap-6 lg:gap-8">
            <svg id="palletBarcode" class="rounded-lg bg-gray-100" />
        </div>
    </div>
</template>