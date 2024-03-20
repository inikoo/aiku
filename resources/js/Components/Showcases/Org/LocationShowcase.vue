<script setup lang='ts'>
import { ref, onMounted } from 'vue'
import JsBarcode from 'jsbarcode'
import TabSelector from '@/Components/Elements/TabSelector.vue'
import { routeType } from '@/types/route'

const props = defineProps<{
    data: {
        updateRoute: routeType
        radioTabs: any
    }
}>()

// console.log('rrrr', props.data)

// Tabs radio: v-model
const radioValue = ref<string[]>(Object.keys(props.data.radioTabs).filter(key => props.data.radioTabs[key]))
// const radioValue = ref<string[]>(props.data?.radioTabs ? Object.keys(props.data.radioTabs).filter(key => props.data.radioTabs[key]) : ['allow_stocks', 'allow_fulfilment', 'allow_dropshipping'])

// Tabs radio: options
const optionRadio = [
    {
        value: 'allow_stocks',
        label: 'Allow stocks'
    },
    {
        value: 'allow_fulfilment',
        label: 'Allow fulfilment'
    },
    {
        value: 'allow_dropshipping',
        label: 'Allow dropshipping'
    },
]

onMounted(() => {
    JsBarcode('#locationBarcode', route().v().params.location.padStart(4, 0), {
        lineColor: "rgb(41 37 36)",
        width: 2,
        height: 70,
        displayValue: true
    });
})


</script>

<template>
    {{ data }}
    <div class="w-full grid grid-cols-2">
        <!-- Section: Radio -->
        <div class="px-8 mt-4">
            <TabSelector :optionRadio="optionRadio" :radioValue="radioValue" :updateRoute="data?.updateRoute"/>
        </div>

        <!-- Section: Barcode -->
        <div class="place-self-end pr-2 pt-2">
            <svg id="locationBarcode" />
        </div>
    </div>
</template>