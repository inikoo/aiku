<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Website} from "@/types/website";
import Icon from '@/Components/Icon.vue'
import { library } from "@fortawesome/fontawesome-svg-core";
import { faSkull} from "@fal"

library.add(faSkull)

defineProps<{
    data: object,
    tab?:string
}>()


function websiteShopRoute(website: Website) {
  return route(
    'grp.org.shops.show.web.websites.show',
    [route().params.organisation, website.shop_slug, website.slug]);
}

function websiteFulfilmentRoute(website: Website) {
  return route(
    'grp.org.fulfilments.show.web.websites.show',
    [route().params.organisation, website.fulfilment_slug, website.slug]
  );
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: website }">
            <Link v-if="website.shop_type==='fulfilment'" :href="websiteFulfilmentRoute(website)" class="primaryLink">
                {{ website['code'] }}
            </Link>
          <Link v-else :href="websiteShopRoute(website)" class="primaryLink">
            {{ website['code'] }}
          </Link>
        </template>

        <template #cell(state)="{ item: website }">
            <Icon :data="website['state_icon']" class="px-1"/>
        </template>

    </Table>


</template>


