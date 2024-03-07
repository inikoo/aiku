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
import Button from '@/Components/Elements/Buttons/Button.vue'

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
        <!-- Column: State -->
		<template #cell(state)="{ item: palletDelivery }">
            <div v-if="app == 'retina'" class="px-3">
                <TagPallete :stateIcon="palletDelivery.state_icon" />
            </div>
			<Icon v-else :data="palletDelivery['state_icon']" class="px-1" />
		</template>

        <!-- Column: Actions -->
        <template #cell(actions)="{ item: pallet }" v-if="props.state == 'in-process'">
            <div>
                <Link :href="customerRoute(pallet)" method="delete">
                    <!-- <font-awesome-icon class="text-red-600" :icon="['far', 'trash-alt']" /> -->
                    <Button icon="fal fa-trash-alt" type="negative" />
                </Link>
            </div>
        </template>

        <!-- Column: Stored Items -->
        <template #cell(stored_items)="{ item: pallet }">
            <div v-if="pallet.stored_items.length" class="flex flex-wrap gap-x-1 gap-y-1.5">
                <Tag v-for="item of pallet.stored_items" :theme="item.id" :label="`${item.reference} (${item.quantity})`" :closeButton="false"
                    :stringToColor="true">
                    <template #label>
                        <div class="whitespace-nowrap text-xs">
                            {{ item.reference }} (<span class="font-light">{{ item.quantity }}</span>)
                        </div>
                    </template>
                </Tag>
            </div>
            <div v-else class="text-gray-400 text-xs italic">
                No items in this pallet
            </div>

        </template>
    </Table>
</template>
