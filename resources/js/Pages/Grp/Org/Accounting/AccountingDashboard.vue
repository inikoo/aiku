<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 01 Mar 2023 14:57:23 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import {Head, Link} from '@inertiajs/vue3';
import axios from 'axios';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import {computed, inject, ref} from 'vue';
import {aikuLocaleStructure} from '@/Composables/useLocaleStructure';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import PaymentMethodsWidget from '@/Components/Accounting/PaymentMethodsWidget.vue';
import SegmentedToggle from '@/Components/Utils/SegmentedToggle.vue';
import { ctrans } from "@/Composables/useTrans"
import { capitalize } from "@/Composables/capitalize"
import {library} from '@fortawesome/fontawesome-svg-core';
import {
  faMoneyCheckAlt, faCashRegister, faFileInvoiceDollar, faCoins, faExclamationTriangle, faChartNetwork, faBars, faPiggyBank
} from '@fal';
const props = defineProps(['title', 'pageHead', 'flatTreeMaps', 'payment_methods', 'intervals', 'settings', 'uncostedStockDeliveries']);



const locale = inject('locale', aikuLocaleStructure);
const paymentInterval = ref(props.intervals?.value);
const paymentIntervalOptions = [
    {label: ctrans('1M'), value: '1m'},
    {label: ctrans('1Q'), value: '1q'},
    {label: ctrans('1Y'), value: '1y'},
];
const paymentPeriod = computed(() => props.payment_methods?.periods?.[paymentInterval.value]);
const savePaymentInterval = (interval) => {
    axios.patch(route('grp.models.profile.update'), {settings: {selected_interval: interval}});
};
const accountingLinks = computed(() => (props.flatTreeMaps ?? []).flat());
const metricDots = ['bg-amber-300', 'bg-amber-500', 'bg-amber-700'];

library.add(faCoins, faMoneyCheckAlt, faCashRegister, faFileInvoiceDollar, faExclamationTriangle, faChartNetwork, faBars, faPiggyBank);

</script>

<template>
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>
    <nav :aria-label="ctrans('Accounting')" class="mx-4 mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
        <div v-for="link in accountingLinks" :key="link.name" class="flex items-stretch rounded-lg border border-gray-200 bg-white text-sm">
            <Link :href="route(link.route.name, link.route.parameters)" class="flex flex-1 items-center gap-2 rounded-l-lg px-3 py-2 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                <FontAwesomeIcon v-if="link.icon" :icon="link.icon" class="text-gray-400" fixed-width aria-hidden="true" />
                <span class="truncate">{{ link.name }}</span>
                <span v-if="link.index" class="ml-auto font-semibold tabular-nums text-gray-800">{{ locale.number(link.index.number) }}</span>
            </Link>
            <Link v-if="link.rightSubLink" :href="route(link.rightSubLink.route.name, link.rightSubLink.route.parameters)" v-tooltip="link.rightSubLink.tooltip" class="flex items-center rounded-r-lg border-l border-gray-200 px-2.5 text-gray-400 hover:bg-gray-50 hover:text-gray-700">
                <FontAwesomeIcon :icon="link.rightSubLink.icon" fixed-width :aria-label="link.rightSubLink.tooltip" />
            </Link>
        </div>
    </nav>
    <div class="mx-4 mt-3 grid grid-cols-1 gap-3 lg:grid-cols-2 2xl:grid-cols-3">
        <PaymentMethodsWidget v-if="paymentPeriod" :summary="{ ...paymentPeriod.summary, period_label: paymentPeriod.period_label }" :tableRoute="payment_methods.route">
            <template #actions>
                <SegmentedToggle v-model="paymentInterval" :options="paymentIntervalOptions" :ariaLabel="ctrans('Period')" @update:modelValue="savePaymentInterval" />
            </template>
        </PaymentMethodsWidget>
        <section v-if="uncostedStockDeliveries" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div class="font-medium text-gray-700">{{ ctrans("Supplier deliveries costing") }}</div>
                <component
                    :is="uncostedStockDeliveries.route ? Link : 'span'"
                    :href="uncostedStockDeliveries.route ? route(uncostedStockDeliveries.route.name, uncostedStockDeliveries.route.parameters) : undefined"
                    v-tooltip="uncostedStockDeliveries.description"
                    class="flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-800">
                    <FontAwesomeIcon :icon="uncostedStockDeliveries.icon" class="text-amber-600" fixed-width aria-hidden="true" />
                    {{ uncostedStockDeliveries.label }}
                    <span class="font-semibold tabular-nums text-gray-800">{{ locale.number(uncostedStockDeliveries.value ?? 0) }}</span>
                </component>
            </div>
            <table class="w-full text-sm tabular-nums">
                <tbody>
                    <tr v-for="(metric, metricIndex) in uncostedStockDeliveries.metrics" :key="metric.label" class="hover:bg-gray-50">
                        <td class="whitespace-nowrap py-0.5 pr-3">
                            <Link :href="route(metric.route.name, metric.route.parameters)" class="flex items-center gap-2 text-gray-700 hover:text-gray-900">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full" :class="metricDots[metricIndex % metricDots.length]" />
                                {{ metric.label }}
                            </Link>
                        </td>
                        <td class="w-full whitespace-nowrap py-0.5 pr-3 text-xs text-gray-400">{{ metric.description }}</td>
                        <td class="py-0.5 text-right font-semibold text-gray-800">{{ locale.number(metric.value) }}</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </div>
</template>

