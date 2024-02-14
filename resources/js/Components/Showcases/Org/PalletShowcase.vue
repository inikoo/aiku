<script setup lang='ts'>
import { ref, onMounted } from 'vue'
import JsBarcode from 'jsbarcode'


const props = defineProps<{
    data: object
}>()

const buf = ref('weqewq')


const bluprint = [
    {
        name: 'Slug',
        value: props.data.data.tabSlug
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
        value: props.data.data.location
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
    JsBarcode('#palletBarcode', route().v().params.pallet.padStart(4, 0), {
        lineColor: "rgb(41 37 36)",
        width: 2,
        height: 70,
        displayValue: true
    });
})

console.log('props', props)
</script>
<template>
    <div class="bg-white">
      <div class="mx-auto grid max-w-2xl grid-cols-1 items-center gap-x-8 gap-y-16  lg:max-w-7xl lg:grid-cols-2 lg:px-8">
        <div>
          <dl class="mt-16 grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 sm:gap-y-16 lg:gap-x-8">
            <div v-for="feature in bluprint" :key="feature.name" class="border-t border-gray-200 pt-4">
              <dt class="font-medium text-gray-900">{{ feature.name }}</dt>
              <dd class="mt-2 text-sm text-gray-500">{{ feature.value }}</dd>
            </div>
          </dl>
        </div>
        <div class="grid grid-cols-2 grid-rows-2 gap-4 sm:gap-6 lg:gap-8">
            <svg id="palletBarcode" class="rounded-lg bg-gray-100" />
        </div>
      </div>
    </div>
  </template>
  