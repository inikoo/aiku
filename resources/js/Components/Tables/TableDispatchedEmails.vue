<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {DispatchedEmail} from "@/types/dispatched-email";
import {
    faCheck,
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
  faVirus,
} from "@fal";
import { library } from "@fortawesome/fontawesome-svg-core";
import Icon from '../Icon.vue'
import { inject } from 'vue'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'
import { useFormatTime } from '@/Composables/useFormatTime'

library.add(
    faSpellCheck,
    faPaperPlane,
    faExclamationCircle,
    faVirus,
    faInboxIn,
    faMousePointer,
    faExclamationTriangle,
    faSquare,
    faEnvelopeOpen,
    faMousePointer,
    faDumpster,
    faHandPaper,
    faCheck,
    faTimesCircle,
);
const props = defineProps<{
    data: object,
    tab?: string
}>()


function dispatchedEmailRoute(dispatchedEmail: DispatchedEmail) {
    switch (route().current()) {
        case 'mail.dispatched-emails.index':
            return route(
                'mail.dispatched-emails.show',
                [dispatchedEmail.outbox_id, dispatchedEmail.id]);
        default:
            return route(
                'dispatched-emails.show',
                [dispatchedEmail.id]);
    }
}

const locale = inject('locale', aikuLocaleStructure)


</script>

<template>
    <Table :resource="data" :name="tab"  class="mt-5">
        <template #cell(state)="{ item: dispatchedEmail }">
            <Icon :data="dispatchedEmail.state" />
        </template>
        <template #cell(email_address)="{ item: dispatchedEmail }">
            {{ dispatchedEmail["email_address"]}} <Icon :data="dispatchedEmail.mask_as_spam" />
        </template>
        <template #cell(sent_at)="{ item: dispatchedEmail }">
            {{ useFormatTime(dispatchedEmail.sent_at, { localeCode: locale.language.code, formatTime: "aiku" }) }}
        </template>
        <template #cell(name)="{ item: dispatchedEmail }">
            <Link :href="route(dispatchedEmailRoute(dispatchedEmail))">
                {{ dispatchedEmail["name"] }}
            </Link>
        </template>
    </Table>
</template>


