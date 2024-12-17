<script setup lang="ts">
import { defineProps, HtmlHTMLAttributes, ref } from "vue";
import { Pie } from "vue-chartjs";
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    ArcElement,
} from "chart.js";
import Modal from "@/Components/Utils/Modal.vue"
import { library } from "@fortawesome/fontawesome-svg-core";
import { faInboxOut } from "@fas";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faExpand } from "@fal";
import ScreenView from "@/Components/ScreenView.vue"
import { setIframeView } from "@/Composables/Workshop"
import EmptyState from "@/Components/Utils/EmptyState.vue";
import { faPaperPlane, faVirus, faInboxIn, faExclamationTriangle, faInbox, faMousePointer, faEnvelopeOpen, faHandPaper, faDumpster} from '@fal'
library.add(faPaperPlane, faVirus, faInboxIn, faExclamationTriangle, faInbox, faMousePointer, faEnvelopeOpen, faHandPaper, faDumpster)

ChartJS.register(Title, Tooltip, Legend, ArcElement);

library.add(faInboxOut);

const props = defineProps<{
    data: {
        stats: Array<any>
        compiled_layout: HtmlHTMLAttributes
        state: String
        builder: String
    };
}>();
console.log('asdasd', props)
const previewOpen = ref(false)
const iframeClass = ref('w-full h-full')
const totalValue = (props.data.stats.map((item) => item.value || 0)).reduce((acc, val) => acc + val, 0);
const dataSet = {
    labels: (props.data.stats.map((item) => item.label)),
    datasets: [
        {
            backgroundColor: (props.data.stats.map((item) => item.color)),
            data: (props.data.stats.map((item) => item.value || 0)),
        },
    ],
};


</script>

<template>
    <div class="card p-4">
        <!-- Stats Section -->
        <div class="grid grid-cols-4 md:grid-cols-4 gap-2">
            <div class="md:col-span-4 grid sm:grid-cols-1 md:grid-cols-6 gap-2 h-auto mb-3">
                <div v-for="item in data.stats" :key="item.key" :class="item.class"
                    class="bg-gradient-to-tr flex flex-col justify-between px-6 py-2 rounded-lg shadow-lg sm:h-auto">
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <div class="text-lg font-semibold capitalize">{{ item.label }}</div>
                        </div>
                        <div class="rounded-full bg-white/20 p-2">
                            <FontAwesomeIcon :icon="item.icon" class="text-xl" />
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ item.value }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section: Two Columns -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="h-auto mb-3">
                <div class="bg-white p-4 rounded-lg drop-shadow-2xl overflow-auto relative">
                    <button @click="previewOpen = true"
                        class="absolute top-2 right-2 bg-gray-300 text-white px-2 py-1 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <FontAwesomeIcon :icon="faExpand" />
                    </button>
                    <div v-if="data.compiled_layout" v-html="data.compiled_layout"></div>
                    <div v-else>
                        <EmptyState :data="{
                            title: 'you dont have any preview'
                        }" />
                    </div>
                </div>
            </div>

            <div class="h-auto mb-3">
                <!-- Conditional Rendering for the Chart -->
                <div class="chart-container bg-white p-4 w-full rounded-lg shadow drop-shadow-2xl relative">
                    <Pie :data="dataSet" :options="{
                        responsive: true,
                        maintainAspectRatio: false,
                        /* plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const value = context.raw;
                                        return `${context.label}: ${value} (${value > 1 ? '' : '0'})`;
                                    }
                                }
                            }
                        } */
                    }" />

                    <div v-if="totalValue == 0"
                        class="absolute top-0 left-0 flex justify-center items-center bg-gray-300 rounded-full h-[18rem] w-[18rem]"
                        style="transform: translate(-50%, -50%); top: 56%; left: 50%;">
                        <span class="text-gray-500 text-lg">No Data Available</span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <Modal :isOpen="previewOpen" @onClose="previewOpen = false">
        <div class="border">
            <div class="bg-gray-300">
                <ScreenView @screenView="(e) => iframeClass = setIframeView(e)" />
            </div>
            <div v-html="data.compiled_layout"></div>
        </div>
    </Modal>
</template>

<style lang="scss" scoped>
.chart-container {
    position: relative;
    height: 400px;
    width: 100%;
}

.text-xl {
    font-size: 1.25rem;

    @media (max-width: 640px) {
        font-size: 1rem;
    }
}
</style>
