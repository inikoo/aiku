<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {
    faBan,
    faClock,
    faDumpster,
    faEnvelopeOpen,
    faExclamationCircle,
    faExclamationTriangle,
    faHandPaper,
    faInboxIn,
    faMousePointer,
    faPaperPlane,
    faSpellCheck,
    faSquare,
    faTimesCircle,  
} from "@fal";
import { library } from "@fortawesome/fontawesome-svg-core";
import Icon from '../Icon.vue'
import { inject } from 'vue'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'
import { useFormatTime } from '@/Composables/useFormatTime'
import { faDesktopAlt } from '@far'
import { faMobileAlt, faRobot } from '@fas'

library.add(
    faSpellCheck,
    faPaperPlane,
    faBan,
    faExclamationCircle,
    faInboxIn,
    faMousePointer,
    faExclamationTriangle,
    faSquare,
    faEnvelopeOpen,
    faDumpster,
    faHandPaper,
    faClock,
    faTimesCircle,
    faDesktopAlt,
    faMobileAlt,
    faRobot
);
const props = defineProps<{
    data: object,
    tab?: string
}>()

const locale = inject('locale', aikuLocaleStructure)


</script>

<template>
    <Table :resource="data" :name="tab"  class="mt-5">
        <template #cell(type)="{ item: emailTrackingEvent }">
            <Icon :data="emailTrackingEvent.type" />
        </template>
        <template #cell(device)="{ item: emailTrackingEvent }">
            <Icon :data="emailTrackingEvent.device" /> <span>{{ emailTrackingEvent.device['tooltip'] }}</span>
        </template>
        <template #cell(date)="{ item: emailTrackingEvent }">
            {{ useFormatTime(emailTrackingEvent.date, { localeCode: locale.language.code, formatTime: "aiku" }) }}
        </template>
    </Table>
</template>


