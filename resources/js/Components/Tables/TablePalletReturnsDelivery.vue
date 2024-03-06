<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 25 Jan 2024 11:46:16 Malaysia Time, Bali Office, Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Table from "@/Components/Table/Table.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faTrashAlt, faPaperPlane } from "@far"
import { faSignOutAlt } from "@fal"
import { Link } from "@inertiajs/vue3"
import Tag from "@/Components/Tag.vue"
import TagPallete from '@/Components/TagPallete.vue'

import Icon from "@/Components/Icon.vue"

library.add(faTrashAlt, faSignOutAlt, faPaperPlane)
const props = defineProps<{
    data: object
    tab?: string
    state?: string
    app: string // 'retina'
}>()

function customerRoute(pallet: object) {
    return route(pallet.deleteRoute.name, pallet.deleteRoute.parameters)
}
</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
		<template #cell(state)="{ item: palletDelivery }">
            <div v-if="app == 'retina'" class="px-3">
                <TagPallete :stateIcon="palletDelivery.state_icon" />
            </div>
			<Icon v-else :data="palletDelivery['state_icon']" class="px-1" />
		</template>
        <template #cell(actions)="{ item: pallet }">
            <div v-if="props.state == 'in-process'">
                <Link :href="customerRoute(pallet)" method="delete">
                    <font-awesome-icon class="text-red-600" :icon="['far', 'trash-alt']" />
                </Link>
            </div>
            <div v-else>
                <font-awesome-icon :icon="['far', 'paper-plane']" />
            </div>
        </template>

        <template #cell(stored_items)="{ item: pallet }">
            <div class="flex">
                <div v-for="item of pallet.stored_items" class="cursor-pointer mx-[2px]">
                    <Tag :theme="item.id" :label="`${item.reference} (${item.quantity})`" :closeButton="false"
                        :stringToColor="true">
                        <template #label>
                            <div class="whitespace-nowrap text-xs">
                                {{ item.reference }} (<span class="font-light">{{ item.quantity }}</span>)
                            </div>
                        </template>
                    </Tag>
                </div>
            </div>

        </template>
    </Table>
</template>
