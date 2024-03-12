<script setup lang='ts'>
import { trans } from 'laravel-vue-i18n'
import CountUp from 'vue-countup-v3'
import { Pie } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend, Colors } from 'chart.js'
ChartJS.register(ArcElement, Tooltip, Legend, Colors)


const dummyPalletList = [
    {
        name: 'palletA',
        qty: 5
    },
    {
        name: 'pallet Sanur',
        qty: 13
    },
    {
        name: 'Pallet Jawa',
        qty: 21
    },
    {
        name: 'Pallet Lembenx',
        qty: 7
    },
]

const options = {
    responsive: true,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            // Popup: When the data set is hovered
            // enabled: false,
            titleFont: {
                size: 10,
                weight: 'lighter'
            },
            bodyFont: {
                size: 11,
                weight: 'bold'
            }
        },
    }
}

</script>

<template>
    <div class="px-8 py-6 grid grid-cols-2 gap-x-4">
        <div class="h-fit flex justify-between px-4 py-5 sm:p-6 rounded-lg border border-gray-100 shadow tabular-nums">
            <div class="flex flex-col justify-between">
                <dt class="font-semibold text-gray-400 capitalize">Gelas</dt>
                <dd class="mt-2 flex justify-between gap-x-2">
                    <div class="flex flex-col gap-x-2 gap-y-3 leading-none items-baseline text-2xl font-semibold text-org-500">
                        <!-- In Total -->
                        <div class="flex gap-x-2 items-end">
                            <CountUp :endVal="50" :duration="1.5" :scrollSpyOnce="true" :options="{
                                formattingFn: (value: number) => 60
                            }" />
                            <span class="text-sm font-medium leading-4 text-gray-500 ">{{ trans('in total') }}</span>
                        </div>
                    </div>
                </dd>
            </div>

            <!-- Pie -->
            <div class="w-40 self-end">
                <Pie :data="{
                    labels: Object.entries(dummyPalletList).map(([, value]) => value.name),
                    datasets: [{
                        data: Object.entries(dummyPalletList).map(([, value]) => value.qty),
                        hoverOffset: 4
                    }]
                }" :options="options" />
            </div>
        </div>

        <!-- Mini Table -->
        <div class="border border-gray-100 shadow rounded-md px-5 py-3 text-gray-500">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="font-semibold leading-6">Pallets</h1>
                    <p class="text-sm font-light">
                        A list of pallet that contain item <span class="font-semibold">{{ 'Gelas' }}</span>
                    </p>
                </div>
                <!-- <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <button type="button"
                        class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Add
                        user</button>
                </div> -->
            </div>
            <div class="mt-3 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead>
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold sm:pl-3">
                                        Pallet name
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold">
                                        Qty
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                <tr v-for="(pallet, index) in dummyPalletList" :key="pallet.name + index" class="even:bg-gray-50">
                                    <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm font-medium sm:pl-3">
                                        {{ pallet.name }}
                                    </td>
                                    <td class="tabular-nums whitespace-nowrap px-3 py-3 text-sm text-gray-500">
                                        {{ pallet.qty }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>