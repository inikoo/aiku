<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 May 2024 18:46:51 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Table from "@/Components/Table/Table.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faTrashAlt, faPaperPlane } from "@far"
import { faSignOutAlt, faTimes, faShare, faCross } from "@fal"
import { Link } from "@inertiajs/vue3"
import Tag from "@/Components/Tag.vue"
import TagPallet from '@/Components/TagPallet.vue'
import '@/Composables/Icon/PalletReturnStateEnum'  // Import all icon for State

import Icon from "@/Components/Icon.vue"
import Button from '@/Components/Elements/Buttons/Button.vue'
import { inject } from 'vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { trans } from "laravel-vue-i18n"
import { layoutStructure } from "@/Composables/useLayoutStructure"

const layout = inject('layout', layoutStructure)

library.add(faTrashAlt, faSignOutAlt, faTimes, faShare, faCross, faPaperPlane)
const props = defineProps<{
    data: {}
    tab?: string
    state?: string
    app?: string // 'retina'
}>()

function customerRoute(pallet: {}) {
    return route(pallet.deleteFromReturnRoute.name, pallet.deleteFromReturnRoute.parameters)
}
</script>

<template>
    <!-- <pre>{{data}}</pre> -->
    <Table :resource="data" :name="tab" class="mt-5">

        <!-- Column: Type Icon -->
		<template #cell(type_icon)="{ item: palletDelivery }">

         <!--    type icon -->
            <div v-if="app == 'retina'" class="px-3" />
            <FontAwesomeIcon v-else v-tooltip="palletDelivery.type_icon.tooltip" :icon='palletDelivery.type_icon.icon' :class='palletDelivery.type_icon.class' fixed-width aria-hidden='true' />

           <!--  state -->
            <div v-if="app == 'retina'" class="px-3">
                <TagPallet :stateIcon="palletDelivery.state_icon" />
            </div>
			<Icon v-else :data="palletDelivery['state_icon']" class="px-1" />

		</template>

        <!-- Column: State -->
		<!-- <template #cell(state)="{ item: palletDelivery }">
            <div v-if="app == 'retina'" class="px-3">
                <TagPallet :stateIcon="palletDelivery.state_icon" />
            </div>
			<Icon v-else :data="palletDelivery['state_icon']" class="px-1" />
		</template> -->

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

        <!-- Column: Location -->
		<template #cell(location)="{ item: palletDelivery }">
            {{ palletDelivery.location_slug }}
		</template>

        <!-- Column: Actions -->
        <template #cell(actions)="{ item: pallet }" v-if="props.state == 'in-process' || props.state == 'picking'">
            <div v-if="props.state == 'in-process'">
                <Link as="div" :href="customerRoute(pallet)" v-tooltip="trans('Unselect this pallet')" method="delete">
                    <Button icon="fal fa-trash-alt" type="negative" />
                </Link>
            </div>

            <!-- State: Pick or not-picked -->
            <div v-if="props.state == 'picking' && layout.app.name == 'Aiku'" class="flex gap-x-1 ">
                <Link v-if="pallet.state !== 'not-picked'" as="div"
                    :href="route(pallet.updateRoute.name, pallet.updateRoute.parameters)"
                    :data="{state: 'not-picked'}"
                    method="patch"
                    v-tooltip="`Set as not picked`"
                >
                    <Button icon="fal fa-times" type="negative" />
                    <!-- <FontAwesomeIcon icon='fal fa-times' class='' fixed-width aria-hidden='true' /> -->
                </Link>

                <Link v-if="pallet.state !== 'picked'" as="div"
                    :href="route(pallet.updateRoute.name, pallet.updateRoute.parameters)"
                    :data="{state: 'picked'}"
                    method="patch"
                    v-tooltip="`Set as picked`"    
                >
                    <Button icon="fal fa-check" type="positive" />
                </Link>
            </div>
        </template>


    </Table>
</template>
