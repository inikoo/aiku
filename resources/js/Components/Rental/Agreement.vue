<script setup lang="ts">
import { ref, onBeforeMount } from 'vue'
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'
import Rental from '@/Components/Rental/Table.vue'
import RentalBluprint from './Bluprint/rental.js'
import ServicesBluprint from './Bluprint/services.js'
import PhysicalGoodsBluprint from './Bluprint/physicalGoods.js'
import { inject } from 'vue'
import { layoutStructure } from '@/Composables/useLayoutStructure'

const layout = inject('layout', layoutStructure)



const props = defineProps<{
    form: any
    fieldName: string
    options?: any
    fieldData?: {
        type: string
        placeholder: string
        readonly?: boolean
        copyButton: boolean
        maxLength?: number
        physical_goods: {
            data: Object
        },
        rentals: {
            data: Object
        },
        services: {
            data: Object
        },
    }
}>()


const tabs = ref([
    {
        title: 'Rentals',
        value: 'rentals',
        key: 'rentals',
        tableBluprint: RentalBluprint
    },
    {
        title: 'Services',
        value: 'services',
        key: 'services',
        tableBluprint: ServicesBluprint
    },
    {
        title: 'Physical Goods',
        value: 'physical_goods',
        key: 'physical_goods',
        tableBluprint: PhysicalGoodsBluprint
    }
])


onBeforeMount(() => {
    props.form[props.fieldName] = {
        rentals: props.fieldData?.rentals.data,
        physical_goods: props.fieldData?.physical_goods.data,
        services: props.fieldData?.services.data,
    }
})

</script>


<template>
    <div class="border-2 rounded-lg p-3">
        <TabGroup>
            <TabList class="flex space-x-8 border-b-2">
                <Tab v-for="tab in tabs" as="template" :key="tab.key" v-slot="{ selected }">
                    <button :style="selected ? {color : layout.app.theme[0], borderBottomColor : layout.app.theme[0]} : {}" 
                    :class="[
                        'whitespace-nowrap border-b-2 py-1.5 px-1 text-sm font-medium focus:ring-0 focus:outline-none mb-2',
                        selected
                            ? `border-org-5s00 text-[${layout.app.theme[0]}]`
                            : `border-transparent text-[${layout.app.theme[0]}] hover:border-[${layout.app.theme[0]}]`,
                    ]">
                        {{ tab.title }}
                    </button>
                </Tab>
            </TabList>

            <TabPanels class="mt-2">
                <TabPanel v-for="(posts, idx) in tabs" :key="idx" :class="[
                    'rounded-xl bg-white p-3',
                    'ring-white/60 ring-offset-2 ring-offset-blue-400 focus:outline-none focus:ring-2',
                ]">
                    <Rental v-bind="props" :bluprint="posts.tableBluprint" />
                </TabPanel>
            </TabPanels>
        </TabGroup>
    </div>
</template>