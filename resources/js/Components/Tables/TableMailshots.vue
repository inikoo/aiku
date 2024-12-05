<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Mailshot} from "@/types/mailshot";
import icon from '@/Components/Icon.vue'
import { faSpellCheck, faSeedling, faPaperPlane, faStop } from '@fal'
import { library } from "@fortawesome/fontawesome-svg-core";
library.add(faSpellCheck, faSeedling, faPaperPlane, faStop )

const props = defineProps<{
    data: object,
    tab?: string
}>()


function mailshotRoute(mailshot: Mailshot) {
    switch (route().current()) {
        case 'grp.org.shops.show.marketing.mailshots.index':
            return route(
                'grp.org.shops.show.marketing.mailshots.show',
                [route().params.organisation, route().params.shop, mailshot.slug]);
        default:
            return route(
                'grp.org.shops.show.marketing.newsletters.show',
                [route().params.organisation, route().params.shop, mailshot.slug]);
    }
}



</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(subject)="{ item: mailshot }">
            <Link :href="mailshotRoute(mailshot)" class="primaryLink">
                {{ mailshot["subject"] }}
            </Link>
        </template>
        <template #cell(state)="{ item: mailshot }">
            <div class="flex justify-center">
                <icon :data="mailshot.state_icon"/>
            </div>
        </template>
    </Table>
</template>


