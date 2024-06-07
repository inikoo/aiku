<script setup lang="ts">
import { ref, onBeforeMount } from 'vue'
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'
import RentalTable from '@/Components/Rental/Table.vue'
import RentalBluprint from './Bluprint/rental.js'
import ServicesBluprint from './Bluprint/services.js'
import PhysicalGoodsBluprint from './Bluprint/physicalGoods.js'
import { inject } from 'vue'
import { layoutStructure } from '@/Composables/useLayoutStructure'
import { cloneDeep } from 'lodash'
import Button from '@/Components/Elements/Buttons/Button.vue'

const layout = inject('layout', layoutStructure)

const props = withDefaults(defineProps<{
    form: any
    tabs?: Array
    fieldName: string
    options?: any
    reset: Boolean
    fieldData?: {
        type: string
        placeholder: string
        readonly?: boolean
        copyButton: boolean
        maxLength?: number
        physical_goods: {
            data: {}
        },
        rentals: {
            data: {}
        },
        services: {
            data: {}
        },
    }
}>(), {
    reset : true,
    tabs: [
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
    ]
})





const resetValue = () => {
    const clonedData = {
        rentals: cloneDeep(props.fieldData?.rentals?.data),
        physical_goods: cloneDeep(props.fieldData?.physical_goods?.data),
        services: cloneDeep(props.fieldData?.services.data),
    };
    props.form.defaults({
        [props.fieldName]: clonedData,
    })
    props.form.reset(props.fieldName)
}



onBeforeMount(() => {
    const clonedData = {
        rentals: cloneDeep(props.fieldData?.rentals?.data),
        physical_goods: cloneDeep(props.fieldData?.physical_goods?.data),
        services: cloneDeep(props.fieldData?.services.data),
    };
    props.form.defaults({
        [props.fieldName]: clonedData,
    })
    props.form.reset(props.fieldName)
})

</script>


<template>
    <div class="border-2 rounded-lg p-3">
        <TabGroup>
            <TabList class="flex space-x-8 border-b-2">
                <Tab v-for="tab in tabs" as="template" :key="tab.key" v-slot="{ selected }">
                    <button
                        :style="selected ? { color: layout.app.theme[0], borderBottomColor: layout.app.theme[0] } : {}"
                        :class="[
                            'whitespace-nowrap border-b-2 py-1.5 px-1 text-sm font-medium focus:ring-0 focus:outline-none mb-2',
                            selected
                                ? `border-org-5s00 text-[${layout.app.theme[0]}]`
                                : `border-transparent text-[${layout.app.theme[0]}] hover:border-[${layout.app.theme[0]}]`,
                        ]">
                        {{ tab.title }}
                        <span v-if="reset">
                            ({{ props.form[props.fieldName][tab.tableBluprint.key].filter(xxx => xxx.price !=
                                xxx.agreed_price).length ? '+' +
                            props.form[props.fieldName][tab.tableBluprint.key].filter(xxx => xxx.price !=
                                xxx.agreed_price).length : 0 }})
                        </span>
                    </button>
                </Tab>
                <div style="margin-left: auto;" v-if="reset">
                    <Button :label="`Reset`" :icon="['fal', 'history']" type="tertiary" @click="resetValue" />
                </div>
            </TabList>

            <TabPanels class="mt-2">
                <TabPanel v-for="(tab, idx) in tabs" :key="idx" :class="[
                    'rounded-xl bg-white p-3',
                    'ring-white/60 ring-offset-2 ring-offset-blue-400 focus:outline-none focus:ring-2',
                ]">
                    <slot name="table" :data="{ p: props, tab: tab }">
                        <RentalTable v-bind="props" :bluprint="tab.tableBluprint" />
                    </slot>

                </TabPanel>
            </TabPanels>
        </TabGroup>
    </div>
</template>