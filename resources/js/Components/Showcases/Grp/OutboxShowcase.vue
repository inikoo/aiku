<script setup lang="ts">
import { HtmlHTMLAttributes, ref } from "vue";
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
import { faPaperPlane, faVirus, faInboxIn, faExclamationTriangle, faInbox, faMousePointer, faEnvelopeOpen, faHandPaper, faDumpster, faDraftingCompass} from '@fal'
import { Link } from "@inertiajs/vue3"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { trans } from "laravel-vue-i18n"
import Dashboard from "@/Components/DataDisplay/Dashboard/Dashboard.vue";
library.add(faPaperPlane, faVirus, faInboxIn, faExclamationTriangle, faInbox, faMousePointer, faEnvelopeOpen, faHandPaper, faDumpster, faDraftingCompass)

ChartJS.register(Title, Tooltip, Legend, ArcElement);

library.add(faInboxOut);

const props = defineProps<{
    data: {
        outbox: {
            slug: string,
            sender : String,
            subject : String
        }
        stats: Array<any>
        compiled_layout: HtmlHTMLAttributes
        dashboard_stats: String
        builder: String,
    };
}>();
console.log(props)
const previewOpen = ref(false)
const iframeClass = ref('w-full h-full')
// const totalValue = (props.data.stats.map((item) => item.value || 0)).reduce((acc, val) => acc + val, 0);
// const dataSet = {
//     labels: (props.data.stats.map((item) => item.label)),
//     datasets: [
//         {
//             backgroundColor: (props.data.stats.map((item) => item.color)),
//             data: (props.data.stats.map((item) => item.value || 0)),
//         },
//     ],
// };

const isLoadingVisit = ref(false)

</script>

<template>
    <div v-if="data.state === 'in_process'">
        <EmptyState :data="{
            title: trans('Outbox is still in process'),
            description: trans('You can edit it in workshop')
        }">
            <template #button-empty-state>
                <Link
                    :href="route('grp.org.fulfilments.show.operations.comms.outboxes.workshop', { organisation: route().params?.organisation, fulfilment: route().params?.fulfilment, outbox: data.outbox?.slug })"
                    @start="() => isLoadingVisit = true" class="mt-4 block w-fit mx-auto">
                <Button label="workshop" type="secondary" icon="fal fa-drafting-compass" :loading="isLoadingVisit" />
                </Link>
            </template>
        </EmptyState>
    </div>

    <div v-else class="card p-4">
        <!-- Stats Section -->
        <Dashboard :dashboard="props.data.dashboard_stats" />
        <!--   <div class="grid grid-cols-4 md:grid-cols-4 gap-2">
            <div class="md:col-span-4 grid sm:grid-cols-1 md:grid-cols-6 gap-2 h-auto mb-3">
                <div v-for="item in data.stats" :key="item.key"
                 
                    class="flex flex-col justify-between px-6 py-2 rounded-lg border sm:h-auto">
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <div class="text-lg font-semibold capitalize">{{ item.label }}</div>
                        </div>
                        <div class="rounded-full p-2">
                            <FontAwesomeIcon :icon="item.icon" class="text-xl" />
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ item.value || 0}}</div>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Bottom Section: Two Columns -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="h-auto mb-3">
                <div class="bg-white p-4 rounded-lg drop-shadow-2xl overflow-auto relative">
                    <button @click="previewOpen = true"
                        class="absolute top-2 right-2 bg-gray-300 text-white px-2 py-1 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <FontAwesomeIcon :icon="faExpand" />
                    </button>

                    <!-- Email Preview Header -->
                    <div class="mb-4 border-b pb-2">
                        <p class="text-sm text-gray-500"><strong>From:</strong> {{ data.outbox.sender || 'Unknown Sender' }}
                        </p>
                        <p class="text-sm text-gray-500"><strong>Subject:</strong> {{ data.outbox.subject || '(No Subject)' }}
                        </p>
                    </div>

                    <!-- Email Content -->
                    <div v-if="data.compiled_layout" v-html="data.compiled_layout"></div>
                    <div v-else>
                        <EmptyState :data="{ title: 'You don’t have any preview' }" />
                    </div>
                </div>
            </div>
        </div>

    </div>



    <!-- <pre>{{ props }}</pre> -->


    <Modal :isOpen="previewOpen" @onClose="previewOpen = false">
        <div class="border">
            <div class="bg-gray-300">
                <ScreenView @screenView="(e) => iframeClass = setIframeView(e)" />
            </div>
            <div class="mb-4 border-b pb-2 p-2">
                <p class="text-sm text-gray-500"><strong>From:</strong> {{ data.outbox.sender || 'Unknown Sender' }}
                </p>
                <p class="text-sm text-gray-500"><strong>Subject:</strong> {{ data.outbox.subject || '(No Subject)' }}
                </p>
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
