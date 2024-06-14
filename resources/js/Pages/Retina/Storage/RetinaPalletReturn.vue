<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import { capitalize } from "@/Composables/capitalize"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { computed, ref, watch } from "vue"
import { useTabChange } from "@/Composables/tab-change"
import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
import Timeline from "@/Components/Utils/Timeline.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Modal from "@/Components/Utils/Modal.vue"
import TablePalletReturn from "@/Components/PalletReturn/tablePalletReturn.vue"
import TablePalletReturnsDelivery from "@/Components/Tables/Grp/Org/Fulfilment/TablePalletReturnPallets.vue"
import { routeType } from '@/types/route'
import { PageHeading as PageHeadingTypes } from  '@/types/PageHeading'
import palletReturnDescriptor from "@/Components/PalletReturn/Descriptor/PalletReturn.ts"

const props = defineProps<{
	title: string
	tabs: {}
	pallets?: {}
	data?: {}
	history?: {}
	pageHead: PageHeadingTypes
	updateRoute: routeType
	uploadRoutes: routeType
    palletRoute : {
		index : routeType,
		store : routeType
	}
}>()

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)
const timeline = ref({ ...props.data.data })
const openModal = ref(false)

const component = computed(() => {
	const components = {
		pallets: TablePalletReturnsDelivery,
		history: TableHistories,
	}
	return components[currentTab.value]
})

watch(
	props,
	(newValue) => {
		timeline.value = newValue.data.data
	},
	{ deep: true }
)

</script>

<template>
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead">
		<template #button-add-pallet="{ action: action }">
			<Button
				:style="action.action.style"
				:label="action.action.label"
				:icon="action.action.icon"
				:iconRight="action.action.iconRight"
				:key="`ActionButton${action.action.label}${action.action.style}`"
				:tooltip="action.action.tooltip"
				@click="() => (openModal = true)"
			/>
		</template>
	</PageHeading>

	<div class="border-b border-gray-200">
		<Timeline :options="timeline.timeline" :state="timeline.state" :slidesPerView="Object.entries(timeline.timeline).length" />
	</div>

    <!-- Todo -->
    <!-- Box: Notes -->
    <!-- <BoxStatPallet class="pb-2 pt-6 px-2" tooltip="Notes">
        <div class="h-full w-full px-2 flex flex-col items-center">
            <PureTextarea full :placeholder="trans('Enter notes for this pallet return')" />
        </div>
    </BoxStatPallet> -->

	<Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    
	<component
		:is="component"
		:data="props[currentTab]"
        :key="timeline.state"
		:state="timeline.state"
		:tab="currentTab"
        app="retina"
    />

	<Modal :isOpen="openModal" @onClose="openModal = false">
		<div class="min-h-72 max-h-96 px-2 overflow-auto">
			<TablePalletReturn 
				:dataRoute="palletRoute.index"
                :saveRoute="palletRoute.store" 
				@onClose="()=>openModal = false" 	
				:descriptor="palletReturnDescriptor"
			/>
		</div>
	</Modal>
</template>
